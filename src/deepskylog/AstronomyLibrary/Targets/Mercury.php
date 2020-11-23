<?php

/**
 * The target class describing Mercury.
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
 * The target class describing Mercury.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Mercury extends Planet
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

        $L = (new Coordinate(252.250906 + 149474.0722491 * $T + 0.00030350 * $T ** 2 + 0.000000018 * $T ** 3, 0, 360))->getCoordinate();
        $a = 0.387098310;
        $e = 0.20563175 + 0.000020407 * $T - 0.0000000283 * $T ** 2 - 0.00000000018 * $T ** 3;
        $i = (new Coordinate(7.004986 + 0.0018215 * $T - 0.00001810 * $T ** 2 + 0.000000056 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(48.330893 + 1.1861883 * $T + 0.00017542 * $T ** 2 + 0.000000215 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(77.456119 + 1.5564776 * $T + 0.00029544 * $T ** 2 + 0.000000009 * $T ** 3, 0, 360))->getCoordinate();
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

        $L = (new Coordinate(252.250906 + 149472.6746358 * $T - 0.00000536 * $T ** 2 + 0.000000002 * $T ** 3, 0, 360))->getCoordinate();
        $a = 0.387098310;
        $e = 0.20563175 + 0.000020407 * $T - 0.0000000283 * $T ** 2 - 0.00000000018 * $T ** 3;
        $i = (new Coordinate(7.004986 - 0.0059516 * $T + 0.00000080 * $T ** 2 + 0.000000043 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(48.330893 - 0.1254227 * $T - 0.00008833 * $T ** 2 - 0.000000200 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(77.456119 + 0.1588643 * $T - 0.00001342 * $T ** 2 - 0.000000007 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
