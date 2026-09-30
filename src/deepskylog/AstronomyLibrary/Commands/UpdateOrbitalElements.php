<?php

namespace deepskylog\AstronomyLibrary\Commands;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Models\AsteroidsOrbitalElements;
use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateOrbitalElements extends Command
{
    /**
     * The number of rows in one insert. With the 12 columns of the asteroids
     * this stays below the 32766 bound parameters of SQLite.
     */
    private const BATCH_SIZE = 2000;

    /**
     * The orbital elements of the comets of the Minor Planet Center: all
     * comets, most for a recent standard epoch, and then the comets that can
     * be observed now, for the current epoch.
     */
    private const MPC_COMETS = [
        'https://www.minorplanetcenter.net/iau/MPCORB/AllCometEls.txt',
        'https://www.minorplanetcenter.net/iau/MPCORB/CometEls.txt',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astronomy:updateOrbitalElements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the orbital elements of comets and asteroids.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->updateComets();
            $this->updateAsteroids();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Updates the orbital elements of the comets.
     *
     * The elements of JPL are used, replaced by those of the Minor Planet
     * Center for the comets in its file. JPL gives the elements of its last
     * orbit solution, whose epoch can be many years old for a periodic comet
     * (2017 for 10P/Tempel 2 in 2026): a two-body orbit propagated that long
     * misses the perturbations by the planets, and puts such a comet degrees
     * away from its position. The Minor Planet Center gives osculating
     * elements for a current epoch and the perihelion of the current
     * apparition, which keeps the positions within an arcminute or so.
     *
     * The comets are upserted on their name instead of truncating the table,
     * so the photometry stored by astronomy:updateCometPhotometry is kept.
     * Comets that are no longer in the files of JPL and the Minor Planet
     * Center are removed.
     */
    private function updateComets(): void
    {
        $handle = $this->download('https://ssd.jpl.nasa.gov/dat/ELEMENTS.COMET', 'comet');

        $comets = [];
        $cnt = 0;
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($cnt > 1) {
                // The first 43 characters are the name
                $name = trim(substr($line, 0, 43));

                if ($name != '') {
                    $comets[$name] = [
                        'name' => $name,
                        // Character 44 - 51 is the epoch
                        'epoch' => intval(substr($line, 44, 8)) + 2400000.5,
                        // Character 52 - 63 is q: perihelion distance in AU
                        'q' => floatval(substr($line, 52, 12)),
                        // Character 64 - 75 is e, the eccentricity of the orbit
                        'e' => floatval(substr($line, 64, 11)),
                        // Character 75 - 85 is i, the inclination of the orbit
                        'i' => floatval(substr($line, 75, 10)),
                        // w: The argument of perihelion
                        'w' => floatval(substr($line, 85, 10)),
                        // node: Longitude of the ascending node
                        'node' => floatval(substr($line, 95, 10)),
                        // Tp: Time of perihelion passage
                        'Tp' => floatval(substr($line, 105, 15)),
                        // Ref: The orbital solution reference
                        'ref' => trim(substr($line, 120)),
                    ];
                }
            }
            $cnt++;
        }
        fclose($handle);

        if (empty($comets)) {
            throw new RuntimeException('No comet orbital elements found in the download.');
        }

        $comets = $this->mergeMpcComets($comets);

        DB::transaction(function () use ($comets) {
            $table = (new CometsOrbitalElements)->getTable();

            $removed = array_diff(DB::table($table)->pluck('name')->all(), array_keys($comets));
            foreach (array_chunk($removed, self::BATCH_SIZE) as $chunk) {
                DB::table($table)->whereIn('name', $chunk)->delete();
            }

            $columns = ['epoch', 'q', 'e', 'i', 'w', 'node', 'Tp', 'ref'];
            foreach (array_chunk(array_values($comets), self::BATCH_SIZE) as $chunk) {
                DB::table($table)->upsert($chunk, ['name'], $columns);
            }
        });
    }

    /**
     * Replaces the elements of JPL by those of the Minor Planet Center.
     *
     * The comets are matched on their designation. A comet that is only in
     * the files of the Minor Planet Center is added under its name there.
     * Lines without an epoch hold older elements for the perihelion, with
     * fewer decimals: for those the elements of JPL are kept when JPL has the
     * comet. When a file cannot be downloaded, the elements of the other
     * files are kept.
     *
     * @param  array  $comets  The comets of JPL, by name
     * @return array The comets, by name
     */
    private function mergeMpcComets(array $comets): array
    {
        $jpl = [];
        foreach (array_keys($comets) as $name) {
            $key = self::designation($name);
            if ($key !== null) {
                $jpl[$key] = $name;
            }
        }

        $mpc = [];
        foreach (self::MPC_COMETS as $url) {
            try {
                $handle = $this->download($url, 'comet (Minor Planet Center)');
            } catch (RuntimeException $e) {
                $this->warn($e->getMessage());

                continue;
            }
            while (($line = fgets($handle)) !== false) {
                $row = self::parseMpcComet(rtrim($line, "\r\n"));
                $key = $row !== null ? self::designation($row['name']) : null;
                if ($row === null || $key === null) {
                    continue;
                }
                if (! $row['has_epoch'] && isset($jpl[$key])) {
                    continue;
                }
                // The later file, with the current epoch, replaces the earlier one
                $mpc[$key] = $row;
            }
            fclose($handle);
        }

        $replaced = 0;
        $added = 0;
        foreach ($mpc as $key => $row) {
            unset($row['has_epoch']);
            if (isset($jpl[$key])) {
                $row['name'] = $jpl[$key];
                $replaced++;
            } else {
                $added++;
            }
            $comets[$row['name']] = $row;
        }

        $this->info("Orbital elements of {$replaced} comets from the Minor Planet Center, {$added} comets added.");

        return $comets;
    }

    /**
     * Parses a line of CometEls.txt or AllCometEls.txt of the Minor Planet Center.
     *
     *   0010P         2026 08  2.1166  1.417741  0.537437  195.4699  117.7969   12.0271  20260924  13.1  4.0  10P/Tempel    MPC xxxxx
     *
     * See https://www.minorplanetcenter.net/iau/info/CometOrbitFormat.html
     *
     * @return array|null The row for the table, or null for an empty line
     */
    public static function parseMpcComet(string $line): ?array
    {
        $name = trim(substr($line, 102, 56));
        if ($name === '' || strlen($line) < 90) {
            return null;
        }

        $year = intval(substr($line, 14, 4));
        $month = intval(substr($line, 19, 2));
        $day = floatval(substr($line, 22, 7));

        // Some fragments have no epoch: their elements are for the perihelion
        $epoch = trim(substr($line, 81, 8));
        $epochJd = strlen($epoch) === 8
            ? Time::getJd(Carbon::create(intval(substr($epoch, 0, 4)), intval(substr($epoch, 4, 2)), intval(substr($epoch, 6, 2)), 0, 0, 0, 'UTC'))
            : Time::getJd(Carbon::create($year, $month, 1, 0, 0, 0, 'UTC')->addSeconds((int) round(($day - 1) * 86400)));

        return [
            'name' => $name,
            'epoch' => $epochJd,
            'q' => floatval(substr($line, 30, 9)),
            'e' => floatval(substr($line, 41, 8)),
            'w' => floatval(substr($line, 51, 8)),
            'node' => floatval(substr($line, 61, 8)),
            'i' => floatval(substr($line, 71, 8)),
            // YYYYMMDD.dddd, as in the file of JPL
            'Tp' => $year * 10000 + $month * 100 + $day,
            'ref' => str_starts_with($ref = trim(substr($line, 159)), 'MPC') ? $ref : trim('MPC '.$ref),
            // Whether the elements are for a standard epoch or for the perihelion
            'has_epoch' => strlen($epoch) === 8,
        ];
    }

    /**
     * The designation of a comet, the same for JPL and the Minor Planet Center.
     *
     * '10P/Tempel 2' (JPL) and '10P/Tempel' (MPC) give '10P',
     * '73P/Schwassmann-Wachmann 3-B' (JPL) and '73P-B/Schwassmann-Wachmann'
     * (MPC) give '73P-B', 'C/2025 A6 (Lemmon)' gives 'C/2025 A6'.
     */
    public static function designation(string $name): ?string
    {
        $name = trim($name);
        if (preg_match('#^(\d+[PDI])(-[A-Z]{1,2})?(?:/|$)#', $name, $m)) {
            if (! empty($m[2])) {
                return $m[1].$m[2];
            }

            // JPL writes the fragment at the end: '73P/Schwassmann-Wachmann 3-B',
            // '101P/Chernykh-B'. Only capitals, so '67P/Churyumov-Gerasimenko'
            // is not a fragment.
            return preg_match('#-([A-Z]{1,2})$#', $name, $f) ? $m[1].'-'.$f[1] : $m[1];
        }
        if (preg_match('#^([PCDXAI]/-?\d{1,4} [A-Z]{1,2}\d*(?:-[A-Z]{1,2})?)(?:\s|$)#', $name, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Replaces the orbital elements of the numbered asteroids.
     *
     * All rows are replaced in one transaction: readers keep seeing the old
     * elements until the new ones are committed, and a failure leaves the old
     * elements in place.
     */
    private function updateAsteroids(): void
    {
        $handle = $this->download('https://ssd.jpl.nasa.gov/dat/ELEMENTS.NUMBR', 'asteroid');

        DB::transaction(function () use ($handle) {
            $table = (new AsteroidsOrbitalElements)->getTable();

            // A delete instead of a truncate: a truncate commits the transaction on MySQL
            DB::table($table)->delete();

            $cnt = 0;
            $rows = 0;
            $batch = [];
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line, "\r\n");
                if ($cnt > 1) {
                    // Characters 7 - 25 is the name
                    $name = trim(substr($line, 7, 18));

                    if ($name != '') {
                        $batch[] = [
                            // Character 0 - 6 is the number
                            'number' => intval(substr($line, 0, 6)),
                            'name' => $name,
                            // Character 25 - 31 is the epoch
                            'epoch' => intval(substr($line, 25, 6)) + 2400000.5,
                            // Character 31 - 42 is a: semi-major axis in AU
                            'a' => floatval(substr($line, 31, 11)),
                            // Character 42 - 53 is e, the eccentricity of the orbit
                            'e' => floatval(substr($line, 42, 11)),
                            // Character 53 - 63 is i, the inclination of the orbit
                            'i' => floatval(substr($line, 53, 10)),
                            // w: The argument of perihelion
                            'w' => floatval(substr($line, 63, 10)),
                            // node: Longitude of the ascending node
                            'node' => floatval(substr($line, 73, 10)),
                            // M: Mean anomaly
                            'M' => floatval(substr($line, 83, 12)),
                            // H: Absolute magnitude
                            'H' => floatval(substr($line, 95, 6)),
                            // G: Magnitude slope parameter
                            'G' => floatval(substr($line, 101, 6)),
                            // Ref: The orbital solution reference
                            'ref' => trim(substr($line, 107)),
                        ];
                        if (count($batch) >= self::BATCH_SIZE) {
                            DB::table($table)->insert($batch);
                            $rows += count($batch);
                            $batch = [];
                        }
                    }
                }
                $cnt++;
            }
            fclose($handle);
            if (! empty($batch)) {
                DB::table($table)->insert($batch);
                $rows += count($batch);
            }

            if ($rows == 0) {
                throw new RuntimeException('No asteroid orbital elements found in the download.');
            }
        });
    }

    /**
     * Downloads a file of JPL to a temporary file.
     *
     * The whole file is downloaded before the database is touched, so a
     * broken download does not change the tables and the transactions do
     * not wait for the network.
     *
     * @param  string  $url  The url of the file
     * @param  string  $type  comet or asteroid, for the error messages
     * @return resource The temporary file, positioned at the start
     */
    private function download(string $url, string $type)
    {
        $remote = @fopen($url, 'r');
        if ($remote === false) {
            throw new RuntimeException("Failed to download $type orbital elements.");
        }

        $expected = null;
        foreach (stream_get_meta_data($remote)['wrapper_data'] ?? [] as $header) {
            if (is_string($header) && preg_match('/^Content-Length:\s*(\d+)/i', $header, $matches)) {
                // After a redirect the last Content-Length is the one of the file
                $expected = intval($matches[1]);
            }
        }

        $local = tmpfile();
        $bytes = stream_copy_to_stream($remote, $local);
        $timedOut = stream_get_meta_data($remote)['timed_out'];
        fclose($remote);

        if ($bytes === false || $timedOut || ($expected !== null && $bytes != $expected)) {
            fclose($local);

            throw new RuntimeException("Incomplete download of the $type orbital elements.");
        }

        rewind($local);

        return $local;
    }
}
