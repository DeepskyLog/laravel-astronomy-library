<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Psr\Http\Message\ResponseInterface;

class UpdateCometPhotometry extends Command
{
    protected $signature = 'astronomy:updateCometPhotometry
        {--target= : Name or designation to process only}
        {--concurrency=4 : Number of simultaneous requests to aerith.net}';

    protected $description = 'Fetch comet photometry (H, K) from Seiichi Yoshida (aerith.net), with JPL SBDB as fallback.';

    /**
     * aerith.net over http: the certificate of its https site is self-signed
     * and expired in 2021, so https only works without verification.
     */
    private const AERITH_CATALOG = 'http://www.aerith.net/comet/catalog/';

    /**
     * The aerith.net pages with all comets, by the number of the periodic
     * comets and by designation. Together they give the page of every comet.
     */
    private const AERITH_INDEXES = ['index-periodic.html', 'index-code.html'];

    private const SBDB_QUERY = 'https://ssd-api.jpl.nasa.gov/sbdb_query.api?fields=full_name,pdes,prefix,M1,K1&sb-kind=c';

    /**
     * The number of apparitions of a periodic comet that are tried, newest
     * first, until one has photometry.
     */
    private const MAX_APPARITIONS = 3;

    private const HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (compatible; laravel-astronomy-library/1.0; +https://github.com/DeepskyLog)',
    ];

    public function handle()
    {
        $this->info('Updating comet photometry from Seiichi Yoshida (aerith.net)');

        // Build Guzzle client options and allow runtime overrides for SSL verification.
        // - `AERITH_VERIFY` env var may be: `true`/`false` (boolean) or a path to a CA bundle file.
        // - `AERITH_CA_BUNDLE` env var may be set to an explicit CA bundle path.
        $clientOptions = ['timeout' => 10, 'headers' => self::HEADERS];

        $aerithVerify = env('AERITH_VERIFY', null);
        if ($aerithVerify !== null) {
            // If explicit boolean-like values are provided, convert them.
            if ($aerithVerify === false || $aerithVerify === 'false' || $aerithVerify === '0') {
                $clientOptions['verify'] = false;
            } elseif ($aerithVerify === true || $aerithVerify === 'true' || $aerithVerify === '1') {
                $clientOptions['verify'] = true;
            } else {
                // Otherwise treat the value as a path to a CA bundle file.
                $clientOptions['verify'] = $aerithVerify;
            }
        } else {
            $caBundle = env('AERITH_CA_BUNDLE', null);
            if ($caBundle) {
                $clientOptions['verify'] = $caBundle;
            }
        }

        $client = new Client($clientOptions);

        $target = $this->option('target');
        if ($target && trim($target) !== '') {
            $this->info("Processing single target: {$target}");
            $t = trim($target);
            // Try exact match first, then a LIKE fallback
            $comets = CometsOrbitalElements::where('name', $t)
                ->orWhere('name', 'like', "%{$t}%")
                ->limit(1)
                ->get();

            if ($comets->isEmpty()) {
                $this->line("No comet found matching '{$target}' in comets_orbital_elements.");

                return 0;
            }
        } else {
            $comets = CometsOrbitalElements::all();
        }

        $sbdb = $this->fetchSbdbPhotometry($client);
        $aerithPages = $this->fetchAerithIndex($client);

        $counts = ['aerith' => 0, 'SBDB' => 0, 'none' => 0];

        $requests = function () use ($comets, $sbdb, $aerithPages, $client, &$counts) {
            foreach ($comets as $comet) {
                $key = $this->aerithKey($comet->name, $sbdb[$comet->name] ?? null);
                $url = $key !== null ? ($aerithPages[$key] ?? null) : null;

                $promise = $url === null
                    ? Create::promiseFor(null)
                    : $this->fetchAerithPhotometry($client, $url);

                yield $promise->then(function ($found) use ($comet, $sbdb, &$counts) {
                    $source = $this->storePhotometry($comet, $found, $sbdb[$comet->name] ?? null);
                    $counts[$source]++;
                });
            }
        };

        $concurrency = max(1, intval($this->option('concurrency')));
        Each::ofLimit($requests(), $concurrency)->wait();

        $this->info("Finished updating comet photometry: {$counts['aerith']} from aerith.net, {$counts['SBDB']} from SBDB, {$counts['none']} without photometry.");

        return 0;
    }

    /**
     * Stores the photometry of aerith.net, or of SBDB when aerith.net has none.
     *
     * @param  array|null  $found  ['H', 'K', 'url'] of aerith.net
     * @param  array|null  $sbdb  The row of the comet in the SBDB query
     * @return string aerith, SBDB or none
     */
    private function storePhotometry(CometsOrbitalElements $comet, ?array $found, ?array $sbdb): string
    {
        if ($found !== null) {
            $this->info("Found photometry for {$comet->name} at {$found['url']}: H={$found['H']} K={$found['K']}, ".count($found['light_curve']).' part(s)');
            if ($this->savePhotometry($comet, $found['H'], $found['K'], null, $found['light_curve'])) {
                return 'aerith';
            }
        }

        if ($sbdb !== null && $sbdb['H'] !== null) {
            $this->info("Found photometry for {$comet->name} via SBDB: H={$sbdb['H']} K={$sbdb['K']}");
            if ($this->savePhotometry($comet, $sbdb['H'], $sbdb['K'], null, [])) {
                return 'SBDB';
            }
        }

        $this->line("No photometry found for {$comet->name}");

        return 'none';
    }

    /**
     * Store the photometry of a comet, for m = H + 5 log(delta) + n log(r) + phase_coeff * alpha.
     *
     * Values outside a plausible range are not stored: they come from parsing
     * something else, like the mean motion in degrees per day or a julian day
     * that ended up in `n` before.
     *
     * The light curve of aerith.net, all its parts with the range of days
     * around the perihelion in which they are valid, is stored in
     * `light_curve`; H and n are those of its most recent part.
     *
     * @param  array  $lightCurve  The parts, see parseAerithPhotometry()
     * @return bool Whether an absolute magnitude was stored
     */
    private function savePhotometry(CometsOrbitalElements $comet, $H, $K, $phase, array $lightCurve = []): bool
    {
        $H = self::plausibleH($H);
        $K = self::plausibleK($K);
        $phase = is_numeric($phase) && $phase >= 0.0 && $phase <= 0.1 ? floatval($phase) : null;
        $lightCurve = array_values(array_filter(
            $lightCurve,
            fn ($part) => self::plausibleH($part['H'] ?? null) !== null && self::plausibleK($part['K'] ?? null) !== null
        ));

        if ($H === null) {
            $this->line("No usable photometry for {$comet->name}");

            return false;
        }

        $comet->H = $H;
        $comet->n = $K;
        $comet->phase_coeff = $phase;
        $comet->light_curve = empty($lightCurve) ? null : $lightCurve;
        $comet->save();

        return true;
    }

    /**
     * The ranges allow the fits of aerith.net for a single period, like
     * H = -17 and K = 35 for a comet far from the Sun, K = 0 for a standstill
     * or K = -5 for a comet that fades while it approaches the Sun.
     */
    private static function plausibleH($H): ?float
    {
        return is_numeric($H) && $H >= -20.0 && $H <= 35.0 ? floatval($H) : null;
    }

    private static function plausibleK($K): ?float
    {
        return is_numeric($K) && $K >= -20.0 && $K <= 60.0 ? floatval($K) : null;
    }

    /**
     * Fetches M1 and K1 of all comets from JPL SBDB in one query.
     *
     * @return array The rows by full name: ['pdes', 'prefix', 'H', 'K']
     */
    private function fetchSbdbPhotometry(Client $client): array
    {
        try {
            $json = json_decode((string) $client->get(self::SBDB_QUERY, ['timeout' => 60])->getBody(), true);
        } catch (\Exception $e) {
            $this->warn('Failed to fetch the SBDB photometry: '.$e->getMessage());

            return [];
        }

        if (! is_array($json) || ! isset($json['fields'], $json['data'])) {
            $this->warn('Unexpected SBDB response, SBDB photometry is not used.');

            return [];
        }

        $col = array_flip($json['fields']);
        $rows = [];
        foreach ($json['data'] as $row) {
            $H = $row[$col['M1']];
            $K = $row[$col['K1']];
            $rows[trim($row[$col['full_name']])] = [
                'pdes' => $row[$col['pdes']],
                'prefix' => $row[$col['prefix']],
                'H' => $H !== null ? floatval($H) : null,
                // An M1 without K1 is treated like an asteroid-like light curve:
                // the H-G system without its phase term is H + 5 log(r delta), so K = 5.
                'K' => $K !== null ? floatval($K) : ($H !== null ? 5.0 : null),
            ];
        }

        return $rows;
    }

    /**
     * Reads the index pages of aerith.net.
     *
     * @return array The url of the page of each comet, by designation: '12P'
     *               or 'C/2025 A6'
     */
    private function fetchAerithIndex(Client $client): array
    {
        $pages = [];
        foreach (self::AERITH_INDEXES as $index) {
            try {
                $html = (string) $client->get(self::AERITH_CATALOG.$index, ['timeout' => 60])->getBody();
            } catch (\Exception $e) {
                $this->warn("Failed to fetch {$index} of aerith.net: ".$e->getMessage());

                continue;
            }

            // <TD><CENTER>C/2025 A6</CENTER></TD><TD><A HREF="2025A6/2025A6.html">Lemmon</A></TD>
            preg_match_all('#<TD><CENTER>([^<]+)</CENTER></TD>\s*<TD><A HREF="([^"]+)"#i', $html, $matches, PREG_SET_ORDER);
            foreach ($matches as $m) {
                $key = trim($m[1]);
                if (! isset($pages[$key])) {
                    $pages[$key] = self::AERITH_CATALOG.$m[2];
                }
            }
        }

        return $pages;
    }

    /**
     * The designation of a comet as used in the index pages of aerith.net.
     *
     * The designation of SBDB is used when available: it tells the fragments
     * of a comet apart, which have no page of their own on aerith.net.
     *
     * @param  string  $name  The name, like '12P/Pons-Brooks' or 'C/2025 A6 (Lemmon)'
     * @param  array|null  $sbdb  The row of the comet in the SBDB query
     * @return string|null '12P', 'C/2025 A6', or null for a fragment
     */
    private function aerithKey(string $name, ?array $sbdb): ?string
    {
        if ($sbdb !== null && $sbdb['pdes'] !== null) {
            $pdes = trim($sbdb['pdes']);
            if (str_contains($pdes, '-')) {
                return null;
            }
            if (preg_match('/^\d+[PDI]$/', $pdes)) {
                return $pdes;
            }
            if ($sbdb['prefix'] !== null) {
                return $sbdb['prefix'].'/'.$pdes;
            }
        }

        if (preg_match('#^(\d+[PDI])/#', $name, $m)) {
            return $m[1];
        }
        if (preg_match('#^([PCDXAI]/\d{4} [A-Z]{1,2}\d*)(?:\s|$)#', $name, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Fetches the photometry of a comet from its page on aerith.net.
     *
     * The page of a numbered periodic comet lists its apparitions; the
     * newest apparitions with a page are tried until one has photometry.
     *
     * @return PromiseInterface Resolves to ['H', 'K', 'url'] or null
     */
    private function fetchAerithPhotometry(Client $client, string $url): PromiseInterface
    {
        if (! str_ends_with($url, '/index.html')) {
            return $this->fetchAerithPages($client, [$url]);
        }

        return $client->getAsync($url)->then(
            function (ResponseInterface $response) use ($client, $url) {
                $apparitions = $this->apparitionPages((string) $response->getBody(), $url);

                return $this->fetchAerithPages($client, array_slice($apparitions, 0, self::MAX_APPARITIONS));
            },
            function () {
                return null;
            }
        );
    }

    /**
     * Fetches the pages one after the other until one has photometry.
     *
     * @return PromiseInterface Resolves to ['H', 'K', 'url'] or null
     */
    private function fetchAerithPages(Client $client, array $urls): PromiseInterface
    {
        if (empty($urls)) {
            return Create::promiseFor(null);
        }

        $url = array_shift($urls);

        return $client->getAsync($url)->then(
            function (ResponseInterface $response) use ($client, $url, $urls) {
                $found = $this->parseAerithPhotometry((string) $response->getBody());
                if ($found !== null) {
                    return $found + ['url' => $url];
                }

                return $this->fetchAerithPages($client, $urls);
            },
            function () use ($client, $urls) {
                return $this->fetchAerithPages($client, $urls);
            }
        );
    }

    /**
     * The pages of the apparitions on the page of a periodic comet, newest first.
     *
     * <TD>12P</TD><TD><CENTER><A HREF="2024.html">2024 Apr. 21</A></CENTER></TD>
     */
    private function apparitionPages(string $html, string $url): array
    {
        preg_match_all('#<A HREF="([^"/]+\.html)">\s*(\d{4})\s#i', $html, $matches, PREG_SET_ORDER);
        usort($matches, fn ($a, $b) => intval($b[2]) - intval($a[2]));

        $base = substr($url, 0, strrpos($url, '/') + 1);

        return array_map(fn ($m) => $base.$m[1], $matches);
    }

    /**
     * The light curve on the page of an apparition.
     *
     * aerith.net lists the light curve in chronological order, one line per
     * period, with the range of days from the perihelion in which it is valid:
     *
     *   m1 = 5.0 + 5 log d + 13.5 log r          [-840,-276]  (2022 Jan.  2 - 2023 July 20)
     *   m1 = 4.3 + 5 log d + 11.0 log r(t + 10)  [  14,    ]  (2024 May   5 - 2024 Oct. 13)
     *   H = 12.5  G = 0.15                       [ 405,    ]  (2027 Sept.11 -             )
     *
     * "log r(t + 10)" uses the distance to the Sun of 10 days later. An
     * asteroid-like line "H = 12.5  G = 0.15" is stored with K = 5, the H-G
     * system without its phase term. The notes below the list, like
     * "* Gray curve is:  m1 = ...", are alternative curves and not used.
     *
     * @return array|null ['H', 'K', 'light_curve']: H and K of the most recent
     *                    part, and all parts as ['from', 'to', 'H', 'K', 'shift']
     */
    public static function parseAerithPhotometry(string $html): ?array
    {
        $number = '[+-]?\d+(?:\.\d*)?';
        $pattern = '/^[ \t]*(?:'
            .'m1\s*=\s*(?<H>'.$number.')\s*\+\s*5(?:\.0*)?\s*log\s*d\s*(?<sign>[+-])\s*(?<K>\d+(?:\.\d*)?)\s*log\s*r'
            .'(?:\s*\(\s*t\s*(?<shiftSign>[+-])\s*(?<shift>\d+(?:\.\d*)?)\s*\))?'
            .'|H\s*=\s*(?<Hg>'.$number.')\s+G\s*=\s*[+-]?\d[\d.]*'
            .')[ \t]*(?:\[\s*(?<from>[+-]?\d+)?\s*,\s*(?<to>[+-]?\d+)?\s*\])?/m';

        if (! preg_match_all($pattern, $html, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL)) {
            return null;
        }

        $parts = [];
        foreach ($matches as $m) {
            if ($m['H'] !== null) {
                $K = floatval($m['K']);
                $shift = $m['shift'] !== null ? floatval($m['shift']) * ($m['shiftSign'] === '-' ? -1 : 1) : 0.0;
                $part = ['H' => floatval($m['H']), 'K' => $m['sign'] === '-' ? -$K : $K, 'shift' => $shift];
            } else {
                $part = ['H' => floatval($m['Hg']), 'K' => 5.0, 'shift' => 0.0];
            }
            $parts[] = [
                'from' => ($m['from'] ?? '') !== '' ? floatval($m['from']) : null,
                'to' => ($m['to'] ?? '') !== '' ? floatval($m['to']) : null,
            ] + $part;
        }

        // An open end runs to the start of the next part: "[14, ]" followed by
        // "[175, ]" means from 14 to 175 days after the perihelion.
        foreach ($parts as $i => $part) {
            if ($part['to'] !== null) {
                continue;
            }
            foreach (array_slice($parts, $i + 1) as $next) {
                if ($next['from'] !== null && ($part['from'] === null || $next['from'] > $part['from'])) {
                    $parts[$i]['to'] = $next['from'];
                    break;
                }
            }
        }

        $last = end($parts);

        return ['H' => $last['H'], 'K' => $last['K'], 'light_curve' => $parts];
    }
}
