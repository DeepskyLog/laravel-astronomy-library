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
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

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
     * Set Orbital Elements.
     *
     * @param float  $a                        Semimajor axis in AU
     * @param float  $e                        Eccentricity
     * @param float  $i                        Inclination
     * @param float  $omega                    Argument of perihelion
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
     * @param float  $epoch        The ep
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, $epoch = 2451545.0, float $height = 0.0): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $geo_coords, $epoch, $height)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay(), $geo_coords, $epoch, $height)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2), $geo_coords, $epoch, $height)
        );
    }

    public function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $epoch, float $height): EquatorialCoordinates
    {
        $nutation = Time::nutation($epoch);

        $sine  = sin(deg2rad($nutation[2]));
        $cose  = cos(deg2rad($nutation[2]));

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

        $diff_in_date = $this->_perihelion_date->diffInSeconds($date) / 3600.0 / 24.0;
        $M            = -$diff_in_date * 0.300171252;

        $E = $this->eccentricAnomaly($this->_e, $M, 0.000001);

        $v = rad2deg(2 * atan(sqrt((1 + $this->_e) / (1 - $this->_e)) * tan(deg2rad($E / 2))));  // Formula 30.1
        $r = $this->_a * (1 - $this->_e * cos(deg2rad($E)));  // Formula 30.2
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

        // Do the calculations again for t - $tau
        $jd      = Time::getJd($date);
        $newDate = Time::fromJd($jd - $tau);

        $diff_in_date = $this->_perihelion_date->diffInSeconds($newDate) / 3600.0 / 24.0;
        $M            = -$diff_in_date * 0.300171252;

        $E = $this->eccentricAnomaly($this->_e, $M, 0.000001);

        $v = rad2deg(2 * atan(sqrt((1 + $this->_e) / (1 - $this->_e)) * tan(deg2rad($E / 2))));  // Formula 30.1
        $r = $this->_a * (1 - $this->_e * cos(deg2rad($E)));  // Formula 30.2
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

        $equa_coords = new EquatorialCoordinates($ra, $dec);

        // Calculate corrections for parallax
        $pi = 8.794 / $delta;

        $siderial_time = Time::apparentSiderialTime($date, new GeographicalCoordinates(0.0, 0.0));

        $hour_angle = (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($equa_coords->getHourAngle($siderial_time) + $geo_coords->getLongitude()->getCoordinate() * 15.0, 0, 360))->getCoordinate();

        $earthsGlobe = $geo_coords->earthsGlobe($height);

        $deltara = rad2deg(atan(-$earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)) / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)))));
        $dec     = rad2deg(atan((sin(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[0] * sin(deg2rad($pi / 3600.0))) * cos(deg2rad($deltara / 3600.0))
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
