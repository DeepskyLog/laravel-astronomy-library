<?php

/**
 * The target class describing Saturn.
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
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;

/**
 * The target class describing Saturn.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Saturn extends Planet
{
    /**
     * Calculates the mean orbital elements.
     *
     * @param Carbon $date The needed date
     *
     * @return array L = mean longitude of the planet
     *               a = semimajor axis of the orbit
     *               e = eccentricity of the orbit
     *               i = inclination on the plane of the ecliptic
     *               omega = longitude of the ascending node
     *               pi = longitude of the perihelion
     *               M = mean anomaly
     *
     * Chapter 31 of Astronomical Algorithms
     */
    public function calculateMeanOrbitalElements(Carbon $date)
    {
        $jd    = Time::getJd($date);
        $T     = ($jd - 2451545.0) / 36525.0;

        $L     = (new Coordinate(50.077444 + 1223.5110686 * $T + 0.00051908 * $T ** 2 - 0.000000030 * $T ** 3, 0, 360))->getCoordinate();
        $a     = 9.554909192 - 0.0000021390 * $T + 0.000000004 * $T ** 2;
        $e     = 0.05554814 - 0.000346641 * $T - 0.0000006436 * $T ** 2 + 0.00000000340 * $T ** 3;
        $i     = (new Coordinate(2.488879 - 0.0037362 * $T - 0.00001519 * $T ** 2 + 0.000000087 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(113.665503 + 0.8770880 * $T - 0.00012176 * $T ** 2 - 0.000002249 * $T ** 3, 0, 360))->getCoordinate();
        $pi    = (new Coordinate(93.057237 + 1.9637613 * $T + 0.00083753 * $T ** 2 + 0.000004928 * $T ** 3, 0, 360))->getCoordinate();
        $M     = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }

    /**
     * Calculates the mean orbital elements in J2000.0.
     *
     * @param Carbon $date The needed date
     *
     * @return array L = mean longitude of the planet
     *               a = semimajor axis of the orbit
     *               e = eccentricity of the orbit
     *               i = inclination on the plane of the ecliptic
     *               omega = longitude of the ascending node
     *               pi = longitude of the perihelion
     *               M = mean anomaly
     *
     * Chapter 31 of Astronomical Algorithms
     */
    public function calculateMeanOrbitalElementsJ2000(Carbon $date)
    {
        $jd    = Time::getJd($date);
        $T     = ($jd - 2451545.0) / 36525.0;

        $L     = (new Coordinate(50.077444 + 1222.1138488 * $T + 0.00021004 * $T ** 2 - 0.000000046 * $T ** 3, 0, 360))->getCoordinate();
        $a     = 9.554909192 - 0.0000021390 * $T + 0.000000004 * $T ** 2;
        $e     = 0.05554814 - 0.000346641 * $T - 0.0000006436 * $T ** 2 + 0.00000000340 * $T ** 3;
        $i     = (new Coordinate(2.488879 + 0.0025514 * $T - 0.00004906 * $T ** 2 + 0.000000017 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(113.665503 - 0.2566722 * $T - 0.00018399 * $T ** 2 + 0.000000480 * $T ** 3, 0, 360))->getCoordinate();
        $pi    = (new Coordinate(93.057237 + 0.5665415 * $T + 0.00052850 * $T ** 2 + 0.000004912 * $T ** 3, 0, 360))->getCoordinate();
        $M     = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
