<?php

/**
 * The target class describing Uranus.
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
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Time;

/**
 * The target class describing Uranus.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Uranus extends Planet
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
        $jd = Time::getJd($date);
        $T = ($jd - 2451545.0) / 36525.0;

        $L = (new Coordinate(314.055005 + 429.8640561 * $T + 0.00030390 * $T ** 2 + 0.000000026 * $T ** 3, 0, 360))->getCoordinate();
        $a = 19.218446062 - 0.0000000372 * $T + 0.00000000098 * $T ** 2;
        $e = 0.04638122 - 0.000027293 * $T + 0.0000000789 * $T ** 2 + 0.00000000024 * $T ** 3;
        $i = (new Coordinate(0.773197 + 0.0007744 * $T + 0.00003749 * $T ** 2 - 0.000000092 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(74.005957 + 0.5211278 * $T + 0.00133947 * $T ** 2 + 0.000018484 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(173.005291 + 1.4863790 * $T + 0.00021406 * $T ** 2 + 0.000000434 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

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
        $jd = Time::getJd($date);
        $T = ($jd - 2451545.0) / 36525.0;

        $L = (new Coordinate(314.055005 + 428.4669983 * $T - 0.00000486 * $T ** 2 + 0.000000006 * $T ** 3, 0, 360))->getCoordinate();
        $a = 19.218446062 - 0.0000000372 * $T + 0.00000000098 * $T ** 2;
        $e = 0.04638122 - 0.000027293 * $T + 0.0000000789 * $T ** 2 + 0.00000000024 * $T ** 3;
        $i = (new Coordinate(0.773197 - 0.0016869 * $T + 0.00000349 * $T ** 2 + 0.000000016 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(74.005957 + 0.0741431 * $T + 0.00040539 * $T ** 2 + 0.000000119 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(173.005291 + 0.0893212 * $T - 0.00009470 * $T ** 2 + 0.000000414 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
