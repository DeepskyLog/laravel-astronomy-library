<?php

/**
 * The target class describing a planet.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;

/**
 * The target class describing a planet.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
abstract class Planet extends Target
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(-0.5667);
    }

    /**
     * Calculates the apparent equatorial coordinates of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateApparentEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // Accept variadic args for compatibility. Optional first arg: $VSOP87 (bool)
        $VSOP87 = $args[0] ?? true;

        // Allow string modes to request external ephemeris (e.g. 'DE440' or 'horizons')
        $useHorizons = false;
        if (is_string($VSOP87)) {
            $mode = strtolower(trim($VSOP87));
            if (in_array($mode, ['horizons', 'de440', 'jpl', 'de'], true)) {
                $useHorizons = true;
            }
        }

        if ($useHorizons) {
            // Use helper script to query JPL/Horizons for apparent RA/Dec.
            $geo = new GeographicalCoordinates(0.0, 0.0);
            $this->setEquatorialCoordinatesToday(
                $this->_horizonsEquatorialCoordinates($date, $geo, 0.0, (string)$VSOP87)
            );
            $this->setEquatorialCoordinatesTomorrow(
                $this->_horizonsEquatorialCoordinates($date->addDay(), $geo, 0.0, (string)$VSOP87)
            );
            $this->setEquatorialCoordinatesYesterday(
                $this->_horizonsEquatorialCoordinates($date->subDays(2), $geo, 0.0, (string)$VSOP87)
            );
            return;
        }

        // Default behavior: use VSOP87 apparent coords
        $this->setEquatorialCoordinatesToday(
            $this->_calculateApparentEquatorialCoordinates($date)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateApparentEquatorialCoordinates($date->addDay())
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateApparentEquatorialCoordinates($date->subDays(2))
        );
    }

    /**
     * Calculates the topocentric equatorial coordinates of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  GeographicalCoordinates  $geo_coords  The geographical coordinates
     * @param  float  $height  The height of the location
     *
     * See chapter 40 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // Expected args: [GeographicalCoordinates $geo_coords, float $height = 0.0, bool $VSOP87 = false]
        $geo_coords = $args[0] ?? null;
        $height = $args[1] ?? 0.0;
        $VSOP87 = $args[2] ?? false;

        // If a string mode is provided (e.g. 'DE440' or 'horizons'), prefer Horizons helper
        $useHorizons = false;
        if (is_string($VSOP87)) {
            $mode = strtolower(trim($VSOP87));
            if (in_array($mode, ['horizons', 'de440', 'jpl', 'de'], true)) {
                $useHorizons = true;
            }
        }

        if (! $geo_coords instanceof GeographicalCoordinates) {
            $geo_coords = new GeographicalCoordinates(0.0, 0.0);
        }

        $height = floatval($height);

        if ($useHorizons) {
            $this->setEquatorialCoordinatesToday(
                $this->_horizonsEquatorialCoordinates($date, $geo_coords, $height, (string)$VSOP87)
            );
            $this->setEquatorialCoordinatesTomorrow(
                $this->_horizonsEquatorialCoordinates($date->addDay(), $geo_coords, $height, (string)$VSOP87)
            );
            $this->setEquatorialCoordinatesYesterday(
                $this->_horizonsEquatorialCoordinates($date->subDays(2), $geo_coords, $height, (string)$VSOP87)
            );
            return;
        }

        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $geo_coords, $height)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay(), $geo_coords, $height)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2), $geo_coords, $height)
        );
    }

    abstract public function calculateHeliocentricCoordinates(Carbon $date);

    private function _calculateApparentEquatorialCoordinates(Carbon $date): EquatorialCoordinates
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $earth = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);

        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $tau = 0.0057755183 * $delta;

        $jd = Time::getJd($date);
        $newDate = Time::fromJd($jd - $tau);

        $helio_coords = $this->calculateHeliocentricCoordinates($newDate);
        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);

        $tau = 0.0057755183 * $delta;

        $lambda = rad2deg(atan2($y, $x));
        $beta = rad2deg(atan2($z, sqrt($x ** 2 + $y ** 2)));

        $T = ($jd - 2451545) / 36525;
        $e = 0.016708634 - 0.000042037 * $T - 0.0000001267 * $T ** 2;
        $pi = 102.93735 + 1.71946 * $T + 0.00046 * $T ** 2;
        $kappa = 20.49552;

        $sun = new Sun();
        $Odot = $sun->calculateOdotBetaR($date)[0];

        $deltaLambda = ((-$kappa * cos(deg2rad($Odot - $lambda)) + $e * $kappa * cos(deg2rad($pi - $lambda))) / cos(deg2rad($beta))) / 3600.0;
        $deltaBeta = (-$kappa * sin(deg2rad($beta)) * (sin(deg2rad($Odot - $lambda)) - $e * sin(deg2rad($pi - $lambda)))) / 3600.0;

        $lambda += $deltaLambda;
        $beta += $deltaBeta;

        $L_accent = $helio_coords[0] - 1.397 * $T - 0.00031 * $T ** 2;

        $deltaLambda = -0.09033 + 0.03916 * cos(deg2rad($L_accent) + sin(deg2rad($L_accent))) * tan(deg2rad($helio_coords[1]));
        $deltaBeta = 0.03916 * (cos(deg2rad($L_accent)) - sin(deg2rad($L_accent)));

        $lambda += $deltaLambda / 3600.0;
        $beta += $deltaBeta / 3600.0;

        $nutation = Time::nutation($jd);

        $lambda += $nutation[0] / 3600.0;

        $ecl = new EclipticalCoordinates($lambda, $beta);

        return $ecl->convertToEquatorial($nutation[3]);
    }

    private function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): EquatorialCoordinates
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $earth = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);

        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $tau = 0.0057755183 * $delta;

        $jd = Time::getJd($date);
        $newDate = Time::fromJd($jd - $tau);

        $helio_coords = $this->calculateHeliocentricCoordinates($newDate);
        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $tau = 0.0057755183 * $delta;

        $lambda = rad2deg(atan2($y, $x));
        $beta = rad2deg(atan2($z, sqrt($x ** 2 + $y ** 2)));

        $T = ($jd - 2451545) / 36525;
        $e = 0.016708634 - 0.000042037 * $T - 0.0000001267 * $T ** 2;
        $pi = 102.93735 + 1.71946 * $T + 0.00046 * $T ** 2;
        $kappa = 20.49552;

        $sun = new Sun();
        $Odot = $sun->calculateOdotBetaR($date)[0];

        $deltaLambda = ((-$kappa * cos(deg2rad($Odot - $lambda)) + $e * $kappa * cos(deg2rad($pi - $lambda))) / cos(deg2rad($beta))) / 3600.0;
        $deltaBeta = (-$kappa * sin(deg2rad($beta)) * (sin(deg2rad($Odot - $lambda)) - $e * sin(deg2rad($pi - $lambda)))) / 3600.0;

        $lambda += $deltaLambda;
        $beta += $deltaBeta;

        $L_accent = $helio_coords[0] - 1.397 * $T - 0.00031 * $T ** 2;

        $deltaLambda = -0.09033 + 0.03916 * cos(deg2rad($L_accent) + sin(deg2rad($L_accent))) * tan(deg2rad($helio_coords[1]));
        $deltaBeta = 0.03916 * (cos(deg2rad($L_accent)) - sin(deg2rad($L_accent)));

        $lambda += $deltaLambda / 3600.0;
        $beta += $deltaBeta / 3600.0;

        $nutation = Time::nutation($jd);

        $lambda += $nutation[0] / 3600.0;

        $ecl = new EclipticalCoordinates($lambda, $beta);

        $equa_coords = $ecl->convertToEquatorial($nutation[3]);

        // Calculate corrections for parallax
        $pi = 8.794 / $delta;

        $siderial_time = Time::apparentSiderialTime($date, new GeographicalCoordinates(0.0, 0.0));

        $hour_angle = (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($equa_coords->getHourAngle($siderial_time) + $geo_coords->getLongitude()->getCoordinate() * 15.0, 0, 360))->getCoordinate();

        $earthsGlobe = $geo_coords->earthsGlobe($height);

        $deltara = rad2deg(atan(-$earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)) / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)))));
        $dec = rad2deg(atan((sin(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[0] * sin(deg2rad($pi / 3600.0))) * cos(deg2rad($deltara / 3600.0))
            / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * cos(deg2rad($height)))));

        $equa_coords->setRA($equa_coords->getRA()->getCoordinate() + $deltara);
        $equa_coords->setDeclination($dec);

        return $equa_coords;
    }

    /**
     * Query the horizons helper for apparent equatorial coordinates using a
     * specified JPL ephemeris (e.g. DE440). Falls back to internal calculation
     * on any error.
     */
    private function _horizonsEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height, string $ephem = 'DE440'): EquatorialCoordinates
    {
        $script = realpath(__DIR__ . '/../../../../scripts/horizons_radec.php');
        if ($script === false || !is_file($script)) {
            return $this->_calculateEquatorialCoordinates($date, $geo_coords, $height);
        }

        // Determine a sensible designation for Horizons (planet name)
        $class = (new \ReflectionClass($this))->getShortName();
        $map = [
            'Mercury' => 'Mercury',
            'Venus' => 'Venus',
            'Mars' => 'Mars',
            'Jupiter' => 'Jupiter',
            'Saturn' => 'Saturn',
            'Uranus' => 'Uranus',
            'Neptune' => 'Neptune',
        ];
        $designation = $map[$class] ?? $class;

        $dt = $date->format('Y-m-d H:i');

        $lon = $geo_coords->getLongitude()->getCoordinate();
        $lat = $geo_coords->getLatitude()->getCoordinate();

        // First try to query the Horizons API directly from PHP to avoid spawning CLI
        $start = $dt;
        $stop = date('Y-m-d H:i', strtotime($dt . ' +1 minute'));
        $site = "'{$lon},{$lat}," . ($height / 1000.0) . "'";

        $post = [
            'format' => 'json',
            'COMMAND' => "'{$designation}'",
            'EPHEM_TYPE' => 'OBSERVER',
            'CENTER' => 'coord@399',
            'SITE_COORD' => $site,
            'START_TIME' => "'{$start}'",
            'STOP_TIME' => "'{$stop}'",
            'STEP_SIZE' => "'1 m'",
            'CSV_FORMAT' => 'YES',
            'EPHEM' => $ephem,
        ];

        $resp = false;
        // Use curl extension if available
        if (function_exists('curl_init')) {
            $ch = curl_init('https://ssd.jpl.nasa.gov/api/horizons.api');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_USERAGENT, 'Deepsky-AstronomyLibrary/1.0');
            $resp = curl_exec($ch);
            curl_close($ch);
        } else {
            // fallback to file_get_contents with stream context
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
                    'content' => http_build_query($post),
                    'user_agent' => 'Deepsky-AstronomyLibrary/1.0',
                    'timeout' => 10,
                ],
            ];
            $context = stream_context_create($opts);
            $resp = @file_get_contents('https://ssd.jpl.nasa.gov/api/horizons.api', false, $context);
        }

        $json = null;
        if ($resp !== false && is_string($resp)) {
            $decoded = @json_decode($resp, true);
            if (is_array($decoded)) {
                // attempt to find a data block inside JSON
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
                            $raStr = $fields[5] ?? ($fields[3] ?? '');
                            $decStr = $fields[6] ?? ($fields[4] ?? '');

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

        // If we couldn't parse HTTP response, fall back to executing helper script (robust parsing handled later)
        if (!is_array($json) || !isset($json['ra_hours']) || !isset($json['dec_deg'])) {
            // Use the same PHP binary the process is running with to avoid mismatched php.ini
            $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
            $parts = [
                escapeshellarg($php),
                escapeshellarg($script),
                escapeshellarg($designation),
                escapeshellarg($dt),
                escapeshellarg((string)$lon),
                escapeshellarg((string)$lat),
                escapeshellarg((string)$height),
                escapeshellarg($ephem),
            ];
            $cmd = implode(' ', $parts) . ' 2>&1';
            $out = [];
            $ret = 0;
            exec($cmd, $out, $ret);
            $resp = implode("\n", $out);

            // Try to extract JSON from helper output
            $maybe = @json_decode($resp, true);
            if (is_array($maybe) && isset($maybe['ra_hours']) && isset($maybe['dec_deg'])) {
                $json = $maybe;
            } elseif (preg_match('/\{[\s\S]*\}/', $resp, $m)) {
                $try = @json_decode($m[0], true);
                if (is_array($try) && isset($try['ra_hours']) && isset($try['dec_deg'])) $json = $try;
            } else {
                $cached = dirname($script) . '/horizons_resp.json';
                if (is_file($cached)) {
                    $try = @json_decode(@file_get_contents($cached), true);
                    if (is_array($try) && isset($try['ra_hours']) && isset($try['dec_deg'])) $json = $try;
                }
            }
        }

        if (!is_array($json) || !isset($json['ra_hours']) || !isset($json['dec_deg'])) {
            return $this->_calculateEquatorialCoordinates($date, $geo_coords, $height);
        }

        $raH = floatval($json['ra_hours']);
        $decD = floatval($json['dec_deg']);

        return new EquatorialCoordinates($raH, $decD);
    }

    /**
     * Calculates the illuminated fraction of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the fraction
     * @return float The illuminated fraction
     *
     * See chapter 41 of Astronomical Algorithms
     */
    public function illuminatedFraction(Carbon $date): float
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $R = $helio_coords[2];

        $earth = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);
        $R0 = $helio_coords_earth[2];

        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);

        $k = (($R + $delta) ** 2 - $R0 ** 2) / (4 * $R * $delta);

        return round($k, 3);
    }
}
