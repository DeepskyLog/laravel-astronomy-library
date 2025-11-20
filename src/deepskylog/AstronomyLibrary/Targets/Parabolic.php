<?php

/**
 * The target class describing an object moving in a parabolic orbit.
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
 * The target class describing an object moving in a parabolic orbit.
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
class Parabolic extends Target
{
    private float $_q;
    private float $_i;
    private float $_omega;
    private float $_longitude_ascending_node;
    private Carbon $_perihelion_date;
    // Photometric parameters for comets: H (absolute), n (activity exponent),
    // optional phase coefficient (mag/deg) and asymmetric n values pre/post perihelion.
    private ?float $_Hc = null;
    private ?float $_n = null;
    private ?float $_phaseCoeff = null;
    private ?float $_n_pre = null;
    private ?float $_n_post = null;

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
     * @param  float  $q  perihelion distance, in AU
     * @param  float  $i  Inclination
     * @param  float  $omega  Argument of perihelion
     * @param  float  $longitude_ascending_node  The Longitude of the Ascending Node
     * @param  Carbon  $perihelion_date  The date of the perihelion
     */
    public function setOrbitalElements(float $q, float $i, float $omega, float $longitude_ascending_node, Carbon $perihelion_date): void
    {
        $this->_q = $q;
        $this->_i = $i;
        $this->_omega = $omega;
        $this->_longitude_ascending_node = $longitude_ascending_node;
        $this->_perihelion_date = $perihelion_date;
    }

    /**
     * Set comet photometric parameters. Parameters:
     *  - $H: absolute magnitude constant
     *  - $n: activity exponent (default 4.0)
     *  - $phaseCoeff: optional linear phase coefficient (mag per degree)
     *  - $n_pre, $n_post: optional asymmetric exponents to use before/after perihelion
     */
    public function setCometParams(float $H, float $n = 4.0, ?float $phaseCoeff = null, ?float $n_pre = null, ?float $n_post = null): void
    {
        $this->_Hc = $H;
        $this->_n = $n;
        $this->_phaseCoeff = $phaseCoeff;
        $this->_n_pre = $n_pre;
        $this->_n_post = $n_post;
    }

    /**
     * Compute comet magnitude using improved model:
     *   m = H + 5 log10(delta) + n * log10(r) + phaseCoeff * phaseAngle
     * where n may be chosen pre/post perihelion when available.
     */
    public function magnitude(Carbon $date): float
    {
        if ($this->getMagnitude() !== null) {
            return $this->getMagnitude();
        }
        if ($this->_Hc === null) {
            return 99.9;
        }

        $diff_in_date = $this->_perihelion_date->diffInSeconds($date) / 3600.0 / 24.0;

        $W = 0.03649116245 / ($this->_q * sqrt($this->_q)) * $diff_in_date;

        $G = $W / 2;
        $Y = pow($G + sqrt($G * $G + 1), 1 / 3);
        $s = $Y - 1 / $Y;
        $v = rad2deg(2 * atan($s));
        $r = $this->_q * (1 + $s * $s);

        // Build heliocentric Cartesian coordinates of the comet (ecliptic frame used here)
        $F = cos(deg2rad($this->_longitude_ascending_node));
        $Gf = sin(deg2rad($this->_longitude_ascending_node)) * 0.917482062;
        $Hf = sin(deg2rad($this->_longitude_ascending_node)) * 0.397777156;

        $P = -sin(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i));
        $Q = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * 0.917482062 - sin(deg2rad($this->_i)) * 0.397777156;
        $R = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * 0.397777156 + sin(deg2rad($this->_i)) * 0.917482062;

        $A = rad2deg(atan2($F, $P));
        $B = rad2deg(atan2($Gf, $Q));
        $C = rad2deg(atan2($Hf, $R));

        $a = sqrt($F ** 2 + $P ** 2);
        $b = sqrt($Gf ** 2 + $Q ** 2);
        $c = sqrt($Hf ** 2 + $R ** 2);

        $x = $r * $a * sin(deg2rad($A + $this->_omega + $v));
        $y = $r * $b * sin(deg2rad($B + $this->_omega + $v));
        $z = $r * $c * sin(deg2rad($C + $this->_omega + $v));

        // Sun geometric coordinates (heliocentric origin used in library)
        $sun = new Sun();
        $XYZ = $sun->calculateGeometricCoordinates($date);

        // Object heliocentric Cartesian (approx): add to Sun cartesian to obtain ecliptic coords
        $xObj = $XYZ->getX()->getCoordinate() + $x;
        $yObj = $XYZ->getY()->getCoordinate() + $y;
        $zObj = $XYZ->getZ()->getCoordinate() + $z;

        // Earth heliocentric coordinates via Earth model
        $earth = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);
        $R0 = $helio_coords_earth[2];
        $L0 = deg2rad($helio_coords_earth[0]);
        $B0 = deg2rad($helio_coords_earth[1]);
        $xE = $R0 * cos($B0) * cos($L0);
        $yE = $R0 * cos($B0) * sin($L0);
        $zE = $R0 * sin($B0);

        // Geocentric vector and distance
        $dx = $xObj - $xE;
        $dy = $yObj - $yE;
        $dz = $zObj - $zE;
        $delta = sqrt($dx ** 2 + $dy ** 2 + $dz ** 2);

        // Phase angle: angle Sun-Object-Earth
        $vxSun = -$xObj; $vySun = -$yObj; $vzSun = -$zObj; // object->Sun
        $vxE = $xE - $xObj; $vyE = $yE - $yObj; $vzE = $zE - $zObj; // object->Earth
        $dot = $vxSun * $vxE + $vySun * $vyE + $vzSun * $vzE;
        $mag1 = sqrt($vxSun ** 2 + $vySun ** 2 + $vzSun ** 2);
        $mag2 = sqrt($vxE ** 2 + $vyE ** 2 + $vzE ** 2);
        $alpha = 0.0;
        if ($mag1 > 0 && $mag2 > 0) {
            $alpha = rad2deg(acos(max(-1.0, min(1.0, $dot / ($mag1 * $mag2)))));
        }

        // Choose n pre/post perihelion when available
        $nVal = $this->_n ?? 4.0;
        if ($this->_n_pre !== null || $this->_n_post !== null) {
            if ($date < $this->_perihelion_date && $this->_n_pre !== null) {
                $nVal = $this->_n_pre;
            } elseif ($date >= $this->_perihelion_date && $this->_n_post !== null) {
                $nVal = $this->_n_post;
            }
        }

        $m = $this->_Hc + 5 * log10($delta) + $nVal * log10($r);
        if ($this->_phaseCoeff !== null) {
            $m += $this->_phaseCoeff * $alpha;
        }

        return floatval($m);
    }

    /**
     * Calculates the equatorial coordinates of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // Expected args: [GeographicalCoordinates $geo_coords, float $height = 0.0]
        $geo_coords = $args[0] ?? null;
        $height = $args[1] ?? 0.0;

        if (! $geo_coords instanceof GeographicalCoordinates) {
            $geo_coords = new GeographicalCoordinates(0.0, 0.0);
        }

        $height = floatval($height);

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

    public function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): EquatorialCoordinates
    {
        $diff_in_date = $this->_perihelion_date->diffInSeconds($date) / 3600.0 / 24.0;

        $F = cos(deg2rad($this->_longitude_ascending_node));
        $G = sin(deg2rad($this->_longitude_ascending_node)) * 0.917482062;
        $H = sin(deg2rad($this->_longitude_ascending_node)) * 0.397777156;

        $P = -sin(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i));
        $Q = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * 0.917482062 - sin(deg2rad($this->_i)) * 0.397777156;
        $R = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * 0.397777156 + sin(deg2rad($this->_i)) * 0.917482062;

        $A = rad2deg(atan2($F, $P));
        $B = rad2deg(atan2($G, $Q));
        $C = rad2deg(atan2($H, $R));

        $a = sqrt($F ** 2 + $P ** 2);
        $b = sqrt($G ** 2 + $Q ** 2);
        $c = sqrt($H ** 2 + $R ** 2);

        $W = 0.03649116245 / ($this->_q * sqrt($this->_q)) * $diff_in_date;

        $G = $W / 2;
        $Y = pow($G + sqrt($G * $G + 1), 1 / 3);
        $s = $Y - 1 / $Y;
        $v = rad2deg(2 * atan($s));
        $r = $this->_q * (1 + $s * $s);

        $x = $r * $a * sin(deg2rad($A + $this->_omega + $v));
        $y = $r * $b * sin(deg2rad($B + $this->_omega + $v));
        $z = $r * $c * sin(deg2rad($C + $this->_omega + $v));

        $sun = new Sun();
        $XYZ = $sun->calculateGeometricCoordinates($date);

        $ksi = $XYZ->getX()->getCoordinate() + $x;
        $eta = $XYZ->getY()->getCoordinate() + $y;
        $zeta = $XYZ->getZ()->getCoordinate() + $z;

        $delta = sqrt($ksi ** 2 + $eta ** 2 + $zeta ** 2);
        $tau = 0.0057755183 * $delta;

        $ra = rad2deg(atan2($eta, $ksi)) / 15.0;
        $dec = rad2deg(asin($zeta / $delta));

        $equa_coords = new EquatorialCoordinates($ra, $dec);

        // Calculate corrections for parallax
        $pi = 8.794 / $delta;

        $siderial_time = Time::apparentSiderialTime($date, new GeographicalCoordinates(0.0, 0.0));

        $hour_angle = (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($equa_coords->getHourAngle($siderial_time) + $geo_coords->getLongitude()->getCoordinate() * 15.0, 0, 360))->getCoordinate();

        $earthsGlobe = $geo_coords->earthsGlobe($height);

        $deltara = rad2deg(atan(-$earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)) / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)))));
        $dec = rad2deg(atan((sin(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[0] * sin(deg2rad($pi / 3600.0))) * cos(deg2rad($deltara / 3600.0))
                                / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * cos(deg2rad($height)))));

        $equa_coords->setRA($ra + $deltara);
        $equa_coords->setDeclination($dec);

        return $equa_coords;
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
        $s = tan(deg2rad($v / 2));

        $t = 27.403895 * ($s ** 3 + 3 * $s) * $this->_q * sqrt($this->_q);

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
        $s = tan(deg2rad($v / 2));

        $t = 27.403895 * ($s ** 3 + 3 * $s) * $this->_q * sqrt($this->_q);

        $JD = Time::getJd($this->_perihelion_date) + $t;

        return Time::fromJd($JD);
    }
}
