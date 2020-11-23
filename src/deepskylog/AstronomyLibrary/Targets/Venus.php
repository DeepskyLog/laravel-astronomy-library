<?php

/**
 * The target class describing Venus.
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
 * The target class describing Venus.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Venus extends Planet
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

        $L = (new Coordinate(181.979801 + 58519.2130302 * $T + 0.00031014 * $T ** 2 + 0.000000015 * $T ** 3, 0, 360))->getCoordinate();
        $a = 0.723329820;
        $e = 0.00677192 - 0.000047765 * $T + 0.0000000981 * $T ** 2 - 0.00000000046 * $T ** 3;
        $i = (new Coordinate(3.394662 + 0.0010037 * $T - 0.00000088 * $T ** 2 - 0.000000007 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(76.679920 + 0.9011206 * $T + 0.00040618 * $T ** 2 - 0.000000093 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(131.563703 + 1.4022288 * $T - 0.00107618 * $T ** 2 - 0.000005678 * $T ** 3, 0, 360))->getCoordinate();
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

        $L = (new Coordinate(181.979801 + 58517.8156760 * $T + 0.00000165 * $T ** 2 - 0.000000002 * $T ** 3, 0, 360))->getCoordinate();
        $a = 0.723329820;
        $e = 0.00677192 - 0.000047765 * $T + 0.0000000981 * $T ** 2 - 0.00000000046 * $T ** 3;
        $i = (new Coordinate(3.394662 - 0.0008568 * $T - 0.00003244 * $T ** 2 + 0.000000009 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(76.679920 - 0.2780134 * $T - 0.00014257 * $T ** 2 - 0.000000164 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(131.563703 + 0.0048746 * $T - 0.00138467 * $T ** 2 - 0.000005695 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
