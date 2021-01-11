<?php

/**
 * The target class describing an object moving in a near-parabolic orbit.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;

/**
 * The target class describing an object moving in a near-parabolic orbit.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class NearParabolic extends Target
{
    private float $_q;
    private float $_e;
    private float $_i;
    private float $_omega;
    private float $_longitude_ascending_node;
    private Carbon $_perihelion_date;

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
     * @param float  $q                        perihelion distance, in AU
     * @param float  $e                        Eccentricity
     * @param float  $i                        Inclination
     * @param float  $omega                    Argument of perihelion
     * @param float  $longitude_ascending_node The Longitude of the Ascending Node
     * @param Carbon $perihelion_date          The date of the perihelion
     */
    public function setOrbitalElements(float $q, float $e, float $i, float $omega, float $longitude_ascending_node, Carbon $perihelion_date): void
    {
        $this->_q                        = $q;
        $this->_e                        = $e;
        $this->_i                        = $i;
        $this->_omega                    = $omega;
        $this->_longitude_ascending_node = $longitude_ascending_node;
        $this->_perihelion_date          = $perihelion_date;
    }

    /**
     * Calculates the equatorial coordinates of the planet.
     *
     * @param Carbon $date      The date for which to calculate the coordinates
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay())
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2))
        );
    }

    public function _calculateEquatorialCoordinates(Carbon $date): EquatorialCoordinates
    {
        $diff_in_date = $this->_perihelion_date->diffInSeconds($date) / 3600.0 / 24.0;

        $k            = 0.01720209895;
        $Q            = $k / (2 * $this->_q) * sqrt((1 + $this->_e) / $this->_q);
        $gamma        = (1 - $this->_e) / (1 + $this->_e);

        if ($diff_in_date == 0) {
            $r = $this->_q;
            $v = 0.0;
        } else {
            $q2 = $Q * $diff_in_date;
            $s  = 2 / (3 * abs($q2));
            $s  = 2 / tan(2 * atan(tan(atan($s) / 2) ** (1 / 3)));
            if ($diff_in_date < 0) {
                $s = -$s;
            }
            if ($this->_e != 1.0) {
                $l = 0;
                do {
                    $s0 = $s;
                    $z  = 1;
                    $y  = $s * $s;
                    $g1 = -$y * $s;
                    $q3 = $q2 + 2 * $gamma * $s * $y / 3;
                    do {
                        $z  = $z + 1;
                        $g1 = -$g1 * $gamma * $y;
                        $z1 = ($z - ($z + 1) * $gamma) / (2 * $z + 1);
                        $f  = $z1 * $g1;
                        $q3 = $q3 + $f;
                    } while (abs($f) > 1e-9 && $z < 500);
                    $l++;
                    do {
                        $s1 = $s;
                        $s  = (2 * $s * $s * $s / 3 + $q3) / ($s * $s + 1);
                    } while (abs($s - $s1) > 1e-9);
                } while (abs($s - $s0) > 1e-9 && $l < 500);
            }
            $v = 2 * atan($s);
            $r = $this->_q * (1 + $this->_e) / (1 + $this->_e * cos($v));
            $v = rad2deg($v);
        }

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

        $x = $r * $a * sin(deg2rad($A + $this->_omega + $v));
        $y = $r * $b * sin(deg2rad($B + $this->_omega + $v));
        $z = $r * $c * sin(deg2rad($C + $this->_omega + $v));

        $sun = new Sun();
        $XYZ = $sun->calculateGeometricCoordinates($date);

        $ksi  = $XYZ->getX()->getCoordinate() + $x;
        $eta  = $XYZ->getY()->getCoordinate() + $y;
        $zeta = $XYZ->getZ()->getCoordinate() + $z;

        $delta = sqrt($ksi ** 2 + $eta ** 2 + $zeta ** 2);
        $tau   = 0.0057755183 * $delta;

        $ra  = rad2deg(atan2($eta, $ksi)) / 15.0;
        $dec = rad2deg(asin($zeta / $delta));

        return new EquatorialCoordinates($ra, $dec);
    }
}
