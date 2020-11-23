<?php

/**
 * The target class describing Jupiter.
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
 * The target class describing Jupiter.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Jupiter extends Planet
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

        $L     = (new Coordinate(34.351519 + 3036.3027748 * $T + 0.00022330 * $T ** 2 + 0.000000037 * $T ** 3, 0, 360))->getCoordinate();
        $a     = 5.202603209 + 0.0000001913 * $T;
        $e     = 0.04849793 + 0.000163225 * $T - 0.0000004714 * $T ** 2 - 0.00000000201 * $T ** 3;
        $i     = (new Coordinate(1.303267 - 0.0054965 * $T + 0.00000466 * $T ** 2 - 0.000000002 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(100.464407 + 1.0209774 * $T + 0.00040315 * $T ** 2 + 0.000000404 * $T ** 3, 0, 360))->getCoordinate();
        $pi    = (new Coordinate(14.331207 + 1.6126352 * $T + 0.00103042 * $T ** 2 - 0.000004464 * $T ** 3, 0, 360))->getCoordinate();
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

        $L     = (new Coordinate(34.351519 + 3034.9056606 * $T - 0.00008501 * $T ** 2 + 0.000000016 * $T ** 3, 0, 360))->getCoordinate();
        $a     = 5.202603209 + 0.0000001913 * $T;
        $e     = 0.04849793 + 0.000163225 * $T - 0.0000004714 * $T ** 2 - 0.00000000201 * $T ** 3;
        $i     = (new Coordinate(1.303267 - 0.0019877 * $T + 0.00003320 * $T ** 2 + 0.000000097 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(100.464407 + 0.1767232 * $T + 0.00090700 * $T ** 2 - 0.000007272 * $T ** 3, 0, 360))->getCoordinate();
        $pi    = (new Coordinate(14.331207 + 0.2155209 * $T + 0.00072211 * $T ** 2 - 0.000004485 * $T ** 3, 0, 360))->getCoordinate();
        $M     = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
