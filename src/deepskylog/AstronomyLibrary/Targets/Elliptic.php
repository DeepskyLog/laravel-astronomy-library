<?php

/**
 * The target class describing an object moving in an elliptic orbit.
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
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;

/**
 * The target class describing an object moving in an elliptic orbit.
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
class Elliptic extends Target
{
    use CometPhotometry;

    private float $_a;
    private float $_e;
    private float $_i;
    private float $_omega;
    private float $_longitude_ascending_node;
    private float $_n;
    private Carbon $_perihelion_date;
    private bool $_useHorizons = false;
    private string $_horizonsDesignation = '';
    // Photometric parameters of an asteroid (IAU H-G system); comets use CometPhotometry
    private ?float $_H = null; // Absolute magnitude H
    private ?float $_G = null; // Slope parameter G

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(-0.5667);
    }

    /**
     * Set Orbital Elements.
     *
     * @param  float  $a  Semimajor axis in AU
     * @param  float  $e  Eccentricity
     * @param  float  $i  Inclination
     * @param  float  $omega  Argument of perihelion
     * @param  float  $longitude_ascending_node  The Longitude of the Ascending Node
     * @param  Carbon  $perihelion_date  The date of the perihelion
     */
    public function setOrbitalElements(float $a, float $e, float $i, float $omega, float $longitude_ascending_node, Carbon $perihelion_date): void
    {
        $this->_a = $a;
        $this->_e = $e;
        // Basic canonical normalization for angles and inclination:
        // - wrap angles into [0,360)
        // - if inclination is negative, make it positive and rotate node/omega by 180deg
        //
        // Inclinations above 90 degrees are retrograde orbits and are left as
        // they are: the formulae of chapter 33 take the inclination through
        // cos(i) and are valid over the full 0 - 180 degree range. Replacing i
        // by 180 - i mirrors the orbit in the ecliptic plane and turns a
        // retrograde orbit (comet Halley, i = 162 degrees) into a prograde one.
        $this->_i = $i;
        $omega_norm = fmod($omega + 360.0, 360.0);
        if ($omega_norm < 0) {
            $omega_norm += 360.0;
        }
        $node_norm = fmod($longitude_ascending_node + 360.0, 360.0);
        if ($node_norm < 0) {
            $node_norm += 360.0;
        }

        if ($this->_i < 0.0) {
            $this->_i = -$this->_i;
            $omega_norm = fmod($omega_norm + 180.0, 360.0);
            $node_norm = fmod($node_norm + 180.0, 360.0);
        }

        $this->_omega = $omega_norm;
        $this->_longitude_ascending_node = $node_norm;
        $this->_n = 0.9856076686 / ($a * sqrt($a));
        $this->_perihelion_date = $perihelion_date;
    }

    /**
     * Set asteroid photometric parameters H and G (IAU H-G system).
     */
    public function setHG(float $H, float $G = 0.15): void
    {
        $this->_H = $H;
        $this->_G = $G;
    }

    /**
     * The magnitude of the object.
     *
     * A comet, with parameters given by setCometParams(), uses the total
     * magnitude m = H + 5 log(delta) + K log(r). An asteroid, with H and G
     * given by setHG(), uses the IAU H-G system (Bowell et al. 1989), as JPL
     * Horizons does for its APmag.
     *
     * @param  Carbon  $date  The date
     * @return float The magnitude, 99.9 when no photometric parameters are known
     */
    public function magnitude(Carbon $date): float
    {
        if ($this->getMagnitude() !== null) {
            return $this->getMagnitude();
        }
        if ($this->hasCometParams()) {
            return $this->_cometMagnitude($date);
        }
        if ($this->_H === null) {
            return 99.9;
        }

        [$r, $delta, $alpha] = $this->_photometricGeometry($date);

        // H-G system phase functions
        $phi1 = exp(-3.33 * pow(tan(deg2rad($alpha) / 2.0), 0.63));
        $phi2 = exp(-1.87 * pow(tan(deg2rad($alpha) / 2.0), 1.22));
        $G = $this->_G ?? 0.15;

        return $this->_H - 2.5 * log10((1 - $G) * $phi1 + $G * $phi2) + 5 * log10($r * $delta);
    }

    /**
     * Enable using JPL Horizons for authoritative ephemerides.
     */
    public function setUseHorizons(bool $use): void
    {
        $this->_useHorizons = $use;
    }

    /**
     * Set the Horizons designation to use (e.g. '12P'). If not set, the
     * object name will be used where possible.
     */
    public function setHorizonsDesignation(string $des): void
    {
        $this->_horizonsDesignation = $des;
    }

    /**
     * Calculates the equatorial coordinates of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  float  $epoch  The ep
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // Expected args: [GeographicalCoordinates $geo_coords, $epoch = 2451545.0, float $height = 0.0]
        $geo_coords = $args[0] ?? null;
        $epoch = $args[1] ?? 2451545.0;
        $height = $args[2] ?? 0.0;

        if (! $geo_coords instanceof GeographicalCoordinates) {
            $geo_coords = new GeographicalCoordinates(0.0, 0.0);
        }

        $epoch = $epoch;
        $height = floatval($height);

        // If Horizons mode is enabled, try to fetch authoritative RA/Dec
        if ($this->_useHorizons) {
            try {
                $h = $this->_horizonsEquatorialCoordinates($date, $geo_coords, $height);
                $this->setEquatorialCoordinatesToday($h);
                $this->setEquatorialCoordinatesTomorrow($this->_horizonsEquatorialCoordinates($date->copy()->addDay(), $geo_coords, $height));
                $this->setEquatorialCoordinatesYesterday($this->_horizonsEquatorialCoordinates($date->copy()->subDay(), $geo_coords, $height));
                return;
            } catch (\Throwable $e) {
                // fallback to internal calculation on failure; log error for debugging
                error_log('Horizons fetch failed: '.$e->getMessage());
            }
        }

        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $geo_coords, $epoch, $height)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->copy()->addDay(), $geo_coords, $epoch, $height)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->copy()->subDay(), $geo_coords, $epoch, $height)
        );
    }

    public function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $epoch, float $height): EquatorialCoordinates
    {
        $XYZ = $this->_sunRectangularCoordinates($date, $epoch);

        [$x, $y, $z] = $this->_heliocentricRectangularCoordinates($date, $epoch);
        $ksi = $XYZ->getX()->getCoordinate() + $x;
        $eta = $XYZ->getY()->getCoordinate() + $y;
        $zeta = $XYZ->getZ()->getCoordinate() + $z;

        $delta = sqrt($ksi ** 2 + $eta ** 2 + $zeta ** 2);
        $tau = 0.0057755183 * $delta;

        // Do the calculations again for t - tau: the object is taken at t - tau,
        // but the Sun (and so the observer) stays at t.
        $newDate = Time::fromJd(Time::getJd($date) - $tau);

        [$x, $y, $z] = $this->_heliocentricRectangularCoordinates($newDate, $epoch);
        $ksi = $XYZ->getX()->getCoordinate() + $x;
        $eta = $XYZ->getY()->getCoordinate() + $y;
        $zeta = $XYZ->getZ()->getCoordinate() + $z;

        $delta = sqrt($ksi ** 2 + $eta ** 2 + $zeta ** 2);

        $ra = rad2deg(atan2($eta, $ksi)) / 15.0;
        $dec = rad2deg(asin($zeta / $delta));

        $equa_coords = new EquatorialCoordinates($ra, $dec);

        // Calculate corrections for parallax.
        // The equatorial horizontal parallax in arcseconds, converted to degrees.
        $equa_coords = $this->_correctForParallax(
            $equa_coords,
            (8.794 / $delta) / 3600.0,
            $date,
            $geo_coords,
            $height
        );

        return $equa_coords;
    }

    /**
     * The heliocentric rectangular coordinates, in AU, referred to the mean
     * equator and equinox of the orbital elements.
     * Chapter 33 of Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date
     * @param  float  $epoch  The equinox of the orbital elements, as a julian day
     * @return array [x, y, z]
     */
    protected function _heliocentricRectangularCoordinates(Carbon $date, float $epoch = 2451545.0): array
    {
        $nutation = Time::nutation($epoch);

        $sine = sin(deg2rad($nutation[2]));
        $cose = cos(deg2rad($nutation[2]));

        $F = cos(deg2rad($this->_longitude_ascending_node));
        $G = sin(deg2rad($this->_longitude_ascending_node)) * $cose;
        $H = sin(deg2rad($this->_longitude_ascending_node)) * $sine;

        $P = -sin(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i));
        $Q = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * $cose - sin(deg2rad($this->_i)) * $sine;
        $R = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * $sine + sin(deg2rad($this->_i)) * $cose;

        $A = rad2deg(atan2($F, $P));
        $B = rad2deg(atan2($G, $Q));
        $C = rad2deg(atan2($H, $R));

        $a = sqrt($F ** 2 + $P ** 2);
        $b = sqrt($G ** 2 + $Q ** 2);
        $c = sqrt($H ** 2 + $R ** 2);

        $diff_in_date = $this->_perihelion_date->diffInSeconds($date, false) / 3600.0 / 24.0;
        $M = $diff_in_date * $this->_n;

        $E = $this->eccentricAnomaly($this->_e, $M, 0.000001);

        $v = rad2deg(2 * atan(sqrt((1 + $this->_e) / (1 - $this->_e)) * tan(deg2rad($E / 2))));  // Formula 30.1
        $r = $this->_a * (1 - $this->_e * cos(deg2rad($E)));  // Formula 30.2

        return [
            $r * $a * sin(deg2rad($A + $this->_omega + $v)),
            $r * $b * sin(deg2rad($B + $this->_omega + $v)),
            $r * $c * sin(deg2rad($C + $this->_omega + $v)),
        ];
    }

    /**
     * Query JPL Horizons for apparent equatorial coordinates (RA hours, Dec deg)
     * for this object on the given date and geographic location.
     * Returns an EquatorialCoordinates instance.
     */
    private function _horizonsEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $heightMeters = 0.0): EquatorialCoordinates
    {
        // Use the external helper script which returns structured JSON.
        $script = realpath(dirname(__DIR__, 4).'/scripts/horizons_radec.php');

        // Require an explicit Horizons designation when using Horizons mode.
        $des = trim((string) $this->_horizonsDesignation);
        if ($des === '') {
            throw new \RuntimeException('No Horizons designation set for Horizons mode');
        }

        if (! $script || ! file_exists($script)) {
            throw new \RuntimeException('Horizons helper script not found at '.dirname(__DIR__, 4).'/scripts/horizons_radec.php');
        }

        $cmd = escapeshellcmd(PHP_BINARY).' '.escapeshellarg($script).' '
            .escapeshellarg($des).' '.escapeshellarg($date->format('Y-m-d H:i')).' '
            .escapeshellarg((string) $geo_coords->getLongitude()->getCoordinate()).' '
            .escapeshellarg((string) $geo_coords->getLatitude()->getCoordinate()).' '
            .escapeshellarg((string) $heightMeters);

        $out = null;
        $ret = null;
        exec($cmd, $out, $ret);
        if ($ret !== 0) {
            throw new \RuntimeException('Horizons helper failed to execute (exit '.intval($ret).')');
        }

        $json = @json_decode(implode("\n", $out), true);
        if (! is_array($json) || ! isset($json['ra_hours']) || ! isset($json['dec_deg'])) {
            throw new \RuntimeException('Invalid JSON from Horizons helper');
        }

        return new EquatorialCoordinates(floatval($json['ra_hours']), floatval($json['dec_deg']));
    }

    private function _hmsToHours(string $s): float
    {
        $s = trim($s);
        if (strpos($s, ':') !== false) {
            [$h, $m, $sec] = explode(':', $s) + [0, 0, 0];

            return intval($h) + intval($m) / 60.0 + floatval($sec) / 3600.0;
        }
        // H M S separated by spaces
        $parts = preg_split('/\s+/', $s);
        if (count($parts) >= 3) {
            return intval($parts[0]) + intval($parts[1]) / 60.0 + floatval($parts[2]) / 3600.0;
        }

        return floatval($s);
    }

    private function _dmsToDegrees(string $s): float
    {
        $s = trim($s);
        $sign = 1;
        if ($s[0] === '+' || $s[0] === '-') {
            if ($s[0] === '-') {
                $sign = -1;
            }
            $s = substr($s, 1);
        }
        if (strpos($s, ':') !== false) {
            [$d, $m, $sec] = explode(':', $s) + [0, 0, 0];

            return $sign * (abs(intval($d)) + intval($m) / 60.0 + floatval($sec) / 3600.0);
        }
        $parts = preg_split('/\s+/', $s);
        if (count($parts) >= 3) {
            return $sign * (abs(intval($parts[0])) + intval($parts[1]) / 60.0 + floatval($parts[2]) / 3600.0);
        }

        return $sign * floatval($s);
    }

    /**
     * Calculates the passage through the nodes.
     *
     * @return Carbon The date of the passage throug the ascending node
     *
     * See chapter 39 of Astronomical Algorithms
     */
    public function ascendingNode(): Carbon
    {
        $v = 360 - $this->_omega;
        $E = 2 * atan(sqrt((1 - $this->_e) / (1 + $this->_e)) * tan(deg2rad($v / 2)));

        $M = rad2deg($E - $this->_e * sin($E));
        $t = $M / $this->_n;

        $JD = Time::getJd($this->_perihelion_date) + $t;

        return Time::fromJd($JD);
    }

    /**
     * Calculates the passage through the nodes.
     *
     * @return Carbon The date of the passage throug the descending node
     *
     * See chapter 39 of Astronomical Algorithms
     */
    public function descendingNode(): Carbon
    {
        $v = 180 - $this->_omega;
        $E = 2 * atan(sqrt((1 - $this->_e) / (1 + $this->_e)) * tan(deg2rad($v / 2)));

        $M = rad2deg($E - $this->_e * sin($E));
        $t = $M / $this->_n;

        $JD = Time::getJd($this->_perihelion_date) + $t;

        return Time::fromJd($JD);
    }
}
