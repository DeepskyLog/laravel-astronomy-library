<?php

/**
 * The target class describing Mars.
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
 * The target class describing Mars.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Mars extends Planet
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

        $L = (new Coordinate(355.433000 + 19141.6964471 * $T + 0.00031052 * $T ** 2 + 0.000000016 * $T ** 3, 0, 360))->getCoordinate();
        $a = 1.523679342;
        $e = 0.09340065 + 0.000090484 * $T - 0.0000000806 * $T ** 2 - 0.00000000025 * $T ** 3;
        $i = (new Coordinate(1.849726 - 0.0006011 * $T + 0.00001276 * $T ** 2 - 0.000000007 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(49.558093 + 0.7720959 * $T + 0.00001557 * $T ** 2 + 0.000002267 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(336.060234 + 1.8410449 * $T - 0.00013477 * $T ** 2 + 0.000000536 * $T ** 3, 0, 360))->getCoordinate();
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

        $L = (new Coordinate(355.433000 + 19140.2993039 * $T + 0.00000262 * $T ** 2 - 0.000000003 * $T ** 3, 0, 360))->getCoordinate();
        $a = 1.523679342;
        $e = 0.09340065 + 0.000090484 * $T - 0.0000000806 * $T ** 2 - 0.00000000025 * $T ** 3;
        $i = (new Coordinate(1.849726 - 0.0081477 * $T - 0.00002255 * $T ** 2 - 0.000000029 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(49.558093 - 0.2950250 * $T - 0.00064048 * $T ** 2 - 0.000001964 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(336.060234 + 0.4439016 * $T - 0.00017313 * $T ** 2 + 0.000000518 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
