<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\Models\AsteroidsOrbitalElements;
use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
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
     * The comets are upserted on their name instead of truncating the table,
     * so the photometry stored by astronomy:updateCometPhotometry is kept.
     * Comets that are no longer in the file of JPL are removed.
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
