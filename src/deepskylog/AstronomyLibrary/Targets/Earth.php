<?php

/**
 * The target class describing Earth.
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
 * The target class describing Earth.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Earth extends Planet
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

        $L = (new Coordinate(100.466457 + 36000.7698278 * $T + 0.00030322 * $T ** 2 + 0.000000020 * $T ** 3, 0, 360))->getCoordinate();
        $a = 1.000001018;
        $e = 0.01670863 - 0.000042037 * $T - 0.0000001267 * $T ** 2 + 0.00000000014 * $T ** 3;
        $i = 0.0;
        $omega = 0.0;
        $pi = (new Coordinate(102.937348 + 1.7195366 * $T + 0.00045688 * $T ** 2 - 0.000000018 * $T ** 3, 0, 360))->getCoordinate();
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

        $L = (new Coordinate(100.466457 + 35999.3728565 * $T - 0.00000568 * $T ** 2 + 0.000000001 * $T ** 3, 0, 360))->getCoordinate();
        $a = 1.000001018;
        $e = 0.01670863 - 0.000042037 * $T - 0.0000001267 * $T ** 2 + 0.00000000014 * $T ** 3;
        $i = 0.0;
        $omega = (new Coordinate(174.873176 - 0.2410908 * $T + 0.00004262 * $T ** 2 + 0.000000001 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(102.937348 + 0.3225654 * $T + 0.00014799 * $T ** 2 - 0.000000039 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
