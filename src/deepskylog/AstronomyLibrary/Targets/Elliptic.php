<?php

/**
 * The target class describing an object moving in an elliptic orbit.
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
use deepskylog\AstronomyLibrary\Coordinates\RectangularCoordinates;

use deepskylog\AstronomyLibrary\Time;

use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;

/**
 * The target class describing an object moving in an elliptic orbit.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Elliptic extends Target
{
    private float $_a;
    private float $_e;
    private float $_i;
    private float $_omega;
    private float $_longitude_ascending_node;
    private float $_n;
    private Carbon $_perihelion_date;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(-0.5667);
    }

    /**
     * Set Orbital Elements
     *
     * @param float  $a                        Semimajor axis in AU
     * @param float  $e                        Eccentricity
     * @param float  $i                        Inclination
     * @param float  $omega                    Argumnet of perihelion
     * @param float  $longitude_ascending_node The Longitude of the Ascending Node
     * @param Carbon $perihelion_date          The date of the perihelion
     */
    public function setOrbitalElements(float $a, float $e, float $i, float $omega, float $longitude_ascending_node, Carbon $perihelion_date): void
    {
        $this->_a                        = $a;
        $this->_e                        = $e;
        $this->_i                        = $i;
        $this->_omega                    = $omega;
        $this->_longitude_ascending_node = $longitude_ascending_node;
        $this->_n                        = 0.9856076686 / ($a * sqrt($a));
        $this->_perihelion_date          = $perihelion_date;
    }

    /**
     * Calculates the equatorial coordinates of the planet.
     *
     * @param Carbon $date      The date for which to calculate the coordinates
     * @param float  $obliquity The obliquity of the ecliptic for the given date
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, float $obliquity): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $obliquity)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay(), $obliquity)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2), $obliquity)
        );
    }

    public function _calculateEquatorialCoordinates(Carbon $date, float $obliquity): EquatorialCoordinates
    {
        $F     = cos(deg2rad($this->_longitude_ascending_node));
        $G     = sin(deg2rad($this->_longitude_ascending_node)) * 0.917482062;
        $H     = sin(deg2rad($this->_longitude_ascending_node)) * 0.397777156;

        $P     = -sin(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i));
        $Q     = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * 0.917482062 - sin(deg2rad($this->_i)) * 0.397777156;
        $R     = cos(deg2rad($this->_longitude_ascending_node)) * cos(deg2rad($this->_i)) * 0.397777156 + sin(deg2rad($this->_i)) * 0.917482062;

        $A     = rad2deg(atan2($F, $P));
        $B     = rad2deg(atan2($G, $Q));
        $C     = rad2deg(atan2($H, $R));

        $a     = sqrt($F ** 2 + $P ** 2);
        $b     = sqrt($G ** 2 + $Q ** 2);
        $c     = sqrt($H ** 2 + $R ** 2);

        $diff_in_date = $this->_perihelion_date->diffInSeconds($date) / 3600.0 / 24.0;
        $M     = -$diff_in_date * 0.300171252;

        $E     = $this->eccentricAnomaly($this->_e, $M, 0.000001);

        $v = rad2deg(2 * atan(sqrt((1 + $this->_e) / (1 - $this->_e)) * tan(deg2rad($E / 2))));  // Formula 30.1
        $r = $this->_a * (1 - $this->_e * cos(deg2rad($E)));  // Formula 30.2
        $x = $r * $a * sin(deg2rad($A + $this->_omega + $v));
        $y = $r * $b * sin(deg2rad($B + $this->_omega + $v));
        $z = $r * $c * sin(deg2rad($C + $this->_omega + $v));

        $sun   = new Sun();
        $XYZ = $sun->calculateGeometricCoordinates($date);

        $ksi   = $XYZ->getX()->getCoordinate() + $x;
        $eta   = $XYZ->getY()->getCoordinate() + $y;
        $zeta  = $XYZ->getZ()->getCoordinate() + $z;

        $delta = sqrt($ksi ** 2 + $eta ** 2 + $zeta ** 2);
        $tau   = 0.0057755183 * $delta;

        // Do the calculations again for t - $tau
        $jd      = Time::getJd($date);
        $newDate = Time::fromJd($jd - $tau);

        $diff_in_date = $this->_perihelion_date->diffInSeconds($newDate) / 3600.0 / 24.0;
        $M     = -$diff_in_date * 0.300171252;

        $E     = $this->eccentricAnomaly($this->_e, $M, 0.000001);

        $v = rad2deg(2 * atan(sqrt((1 + $this->_e) / (1 - $this->_e)) * tan(deg2rad($E / 2))));  // Formula 30.1
        $r = $this->_a * (1 - $this->_e * cos(deg2rad($E)));  // Formula 30.2
        $x = $r * $a * sin(deg2rad($A + $this->_omega + $v));
        $y = $r * $b * sin(deg2rad($B + $this->_omega + $v));
        $z = $r * $c * sin(deg2rad($C + $this->_omega + $v));

        $sun   = new Sun();
        $XYZ = $sun->calculateGeometricCoordinates($date);

        $ksi   = $XYZ->getX()->getCoordinate() + $x;
        $eta   = $XYZ->getY()->getCoordinate() + $y;
        $zeta  = $XYZ->getZ()->getCoordinate() + $z;

        $delta = sqrt($ksi ** 2 + $eta ** 2 + $zeta ** 2);
        $tau   = 0.0057755183 * $delta;

        $ra    = rad2deg(atan2($eta, $ksi)) / 15.0;
        $dec   = rad2deg(asin($zeta / $delta));

        return new EquatorialCoordinates($ra, $dec);
    }
}
