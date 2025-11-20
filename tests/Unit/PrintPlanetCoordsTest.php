<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Mars;
use deepskylog\AstronomyLibrary\Targets\Jupiter;

final class PrintPlanetCoordsTest extends TestCase
{
    public function testPrintMarsAndJupiterVsopAndDE440(): void
    {
        // Random timestamp between 2000-01-01 and 2030-12-31
        $ts = mt_rand(strtotime('2000-01-01'), strtotime('2030-12-31'));
        $date = Carbon::createFromTimestamp($ts, 'UTC');

        // Random geographic location
        $lon = mt_rand(-18000, 18000) / 100.0; // -180..180
        $lat = mt_rand(-9000, 9000) / 100.0;   // -90..90
        $height = mt_rand(0, 3000);

        $geo = new GeographicalCoordinates($lon, $lat);

        $planets = [
            'Mars' => new Mars(),
            'Jupiter' => new Jupiter(),
        ];

        $script = realpath(__DIR__ . '/../../scripts/horizons_radec.php');
        $helperAvailable = $script && file_exists($script);

        fwrite(STDOUT, "\n=== Planet coordinates dump ===\n");
        fwrite(STDOUT, "Date (UTC): {$date->format('Y-m-d H:i:s')}\n");
        fwrite(STDOUT, sprintf("Location: lon=%.5f lat=%.5f height=%dm\n\n", $lon, $lat, $height));

        foreach ($planets as $name => $obj) {
            fwrite(STDOUT, "-- $name --\n");

            // DE440 via Horizons helper (try live first, fall back to per-target cache)
            if (! $helperAvailable) {
                // No helper script; we'll still try curl/remote POST below but mark helper unavailable
            }

            // Quick check: call helper directly to ensure it can produce JSON for this target/time
            $dt = $date->format('Y-m-d H:i');
            $cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script) . ' '
                . escapeshellarg($name) . ' ' . escapeshellarg($dt) . ' '
                . escapeshellarg((string)$lon) . ' ' . escapeshellarg((string)$lat) . ' ' . escapeshellarg((string)$height) . ' ' . escapeshellarg('DE440');

            // Try querying Horizons directly with curl to avoid PHP helper Xdebug issues
            $start = $date->format('Y-m-d H:i');
            $stop = date('Y-m-d H:i', strtotime($start . ' +1 minute'));
            $site = sprintf("'%s,%s,%s'", (string)$lon, (string)$lat, (string)($height / 1000.0));

            $post = http_build_query([
                'format' => 'json',
                'COMMAND' => "'{$name}'",
                'EPHEM_TYPE' => 'OBSERVER',
                'CENTER' => 'coord@399',
                'SITE_COORD' => $site,
                'START_TIME' => "'{$start}'",
                'STOP_TIME' => "'{$stop}'",
                'STEP_SIZE' => "'1 m'",
                'CSV_FORMAT' => 'YES',
                'EPHEM' => 'DE440',
            ]);

            $curlCmd = "curl -s -X POST 'https://ssd.jpl.nasa.gov/api/horizons.api' -d " . escapeshellarg($post);
            $curlOut = null;
            $curlRet = null;
            exec($curlCmd, $curlOut, $curlRet);

            $json = null;
            if ($curlRet === 0 && is_array($curlOut)) {
                $resp = implode("\n", $curlOut);
                // Attempt to decode JSON first
                $decoded = @json_decode($resp, true);
                if (is_array($decoded)) {
                    // Find textual data block similar to helper
                    $block = null;
                    $findBlock = function ($item) use (&$findBlock) {
                        if (is_string($item)) {
                            if (strpos($item, '$$SOE') !== false) return $item;
                            return null;
                        }
                        if (is_array($item)) {
                            foreach ($item as $v) {
                                $res = $findBlock($v);
                                if ($res !== null) return $res;
                            }
                        }
                        return null;
                    };
                    $block = $findBlock($decoded);
                    if ($block === null && isset($decoded['result']) && is_string($decoded['result'])) $block = $decoded['result'];
                    if ($block === null && isset($decoded['data']) && is_string($decoded['data'])) $block = $decoded['data'];

                    if ($block !== null) {
                        // extract first data line
                        if (preg_match('/\$\$SOE([\s\S]*?)\$\$EOE/', $block, $mblock)) {
                            $lines = preg_split('/\r?\n/', trim($mblock[1]));
                            $dataLine = null;
                            foreach ($lines as $ln) {
                                $ln = trim($ln);
                                if ($ln === '') continue;
                                if (strpos($ln, '***') === 0) continue;
                                if (strpos($ln, ',') !== false && preg_match('/\d/', $ln)) {
                                    $dataLine = $ln;
                                    break;
                                }
                            }
                            if ($dataLine !== null) {
                                $fields = array_map('trim', preg_split('/\s*,\s*/', $dataLine));
                                // simple heuristics: RA in HH MM SS at idx 5/6; dec at 6/7 etc.
                                $raStr = $fields[5] ?? ($fields[3] ?? '');
                                $decStr = $fields[6] ?? ($fields[4] ?? '');
                                // convert
                                $hmsToHours = function ($s) {
                                    $s = trim($s);
                                    if (strpos($s, ':') !== false) {
                                        list($h, $m, $sec) = explode(':', $s) + [0, 0, 0];
                                        return intval($h) + intval($m) / 60.0 + floatval($sec) / 3600.0;
                                    }
                                    $parts = preg_split('/\s+/', $s);
                                    if (count($parts) >= 3) return intval($parts[0]) + intval($parts[1]) / 60.0 + floatval($parts[2]) / 3600.0;
                                    return floatval($s);
                                };
                                $dmsToDeg = function ($s) {
                                    $s = trim($s);
                                    $sign = 1;
                                    if ($s[0] === '+' || $s[0] === '-') {
                                        if ($s[0] === '-') $sign = -1;
                                        $s = substr($s, 1);
                                    }
                                    if (strpos($s, ':') !== false) {
                                        list($d, $m, $sec) = explode(':', $s) + [0, 0, 0];
                                        return $sign * (abs(intval($d)) + intval($m) / 60.0 + floatval($sec) / 3600.0);
                                    }
                                    $parts = preg_split('/\s+/', $s);
                                    if (count($parts) >= 3) return $sign * (abs(intval($parts[0])) + intval($parts[1]) / 60.0 + floatval($parts[2]) / 3600.0);
                                    return $sign * floatval($s);
                                };
                                $raH = $hmsToHours($raStr);
                                $decD = $dmsToDeg($decStr);
                                $json = ['ra_hours' => $raH, 'dec_deg' => $decD];
                            }
                        }
                    }
                }
            }

            // fall back to PHP helper or cached file if curl didn't yield usable JSON
            if (! is_array($json) || ! isset($json['ra_hours'])) {
                // try PHP helper
                $out = null;
                $ret = null;
                exec($cmd, $out, $ret);
                if ($ret === 0) {
                    $maybe = @json_decode(implode("\n", $out), true);
                    if (is_array($maybe) && isset($maybe['ra_hours'])) $json = $maybe;
                }
            }

            if (! is_array($json) || ! isset($json['ra_hours'])) {
                // Prefer a per-target cached response (more deterministic for tests)
                $perTarget = __DIR__ . '/../../scripts/horizons_resp_' . $name . '.json';
                $generic = __DIR__ . '/../../scripts/horizons_resp.json';
                if (file_exists($perTarget)) {
                    $savedJson = @json_decode(@file_get_contents($perTarget), true);
                    if (is_array($savedJson) && isset($savedJson['ra_hours'])) {
                        $json = $savedJson;
                        fwrite(STDOUT, "DE440 -> using per-target cached response ({$perTarget})\n");
                        // When using a cached per-target response, align VSOP date with the cached timestamp
                        // The per-target caches were recorded for 2003-01-09 18:41 UTC.
                        $useDate = Carbon::createFromFormat('Y-m-d H:i', '2003-01-09 18:41', 'UTC');
                    }
                } elseif (file_exists($generic)) {
                    $savedJson = @json_decode(@file_get_contents($generic), true);
                    if (is_array($savedJson) && isset($savedJson['ra_hours'])) {
                        $json = $savedJson;
                        fwrite(STDOUT, "DE440 -> using generic cached response ({$generic})\n");
                        // Align VSOP computation with generic cached timestamp if needed
                        $useDate = Carbon::createFromFormat('Y-m-d H:i', '2003-01-09 18:41', 'UTC');
                    }
                }
            }
            if (! isset($useDate)) {
                $useDate = $date;
            }

            // Now compute VSOP87 at the same date used for DE440 (live or cached)
            $obj->calculateEquatorialCoordinates($useDate->copy(), $geo, $height);
            $vsop = $obj->getEquatorialCoordinatesToday();
            if ($vsop instanceof \deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates) {
                fwrite(STDOUT, sprintf("VSOP87 -> RA: %s hours, Dec: %s deg\n", $vsop->printRA(), $vsop->printDeclination()));
            } else {
                fwrite(STDOUT, "VSOP87 -> no coordinates\n");
            }

            if (! is_array($json) || ! isset($json['ra_hours'])) {
                fwrite(STDOUT, "DE440 -> helper/curl failed and no cached response found, skipped\n\n");
                continue;
            }

            fwrite(STDOUT, sprintf("DE440 -> RA: %.10f hours, Dec: %.10f deg\n\n", floatval($json['ra_hours']), floatval($json['dec_deg'])));
        }

        // Always succeed (this test is informational)
        $this->assertTrue(true);
    }
}
