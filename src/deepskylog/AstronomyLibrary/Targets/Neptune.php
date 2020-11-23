<?php

/**
 * The target class describing Neptune.
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
 * The target class describing Neptune.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Neptune extends Planet
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

        $L = (new Coordinate(304.348665 + 219.8833092 * $T + 0.00030882 * $T ** 2 + 0.000000018 * $T ** 3, 0, 360))->getCoordinate();
        $a = 30.110386869 - 0.0000001663 * $T + 0.00000000069 * $T ** 2;
        $e = 0.00945575 + 0.000006033 * $T + 0.0 * $T ** 2 - 0.00000000005 * $T ** 3;
        $i = (new Coordinate(1.769953 - 0.0093082 * $T - 0.00000708 * $T ** 2 + 0.000000027 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(131.784057 + 1.1022039 * $T + 0.00025952 * $T ** 2 - 0.000000637 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(48.120276 + 1.4262957 * $T + 0.00038434 * $T ** 2 + 0.000000020 * $T ** 3, 0, 360))->getCoordinate();
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

        $L = (new Coordinate(304.348665 + 218.4862002 * $T + 0.00000059 * $T ** 2 - 0.000000002 * $T ** 3, 0, 360))->getCoordinate();
        $a = 30.110386869 - 0.0000001663 * $T + 0.00000000069 * $T ** 2;
        $e = 0.00945575 + 0.000006033 * $T + 0.0 * $T ** 2 - 0.00000000005 * $T ** 3;
        $i = (new Coordinate(1.769953 + 0.0002256 * $T + 0.00000023 * $T ** 2 - 0.0 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(131.784057 - 0.0061651 * $T - 0.00000219 * $T ** 2 - 0.000000078 * $T ** 3, 0, 360))->getCoordinate();
        $pi = (new Coordinate(48.120276 + 0.0291866 * $T + 0.00007610 * $T ** 2 + 0.0 * $T ** 3, 0, 360))->getCoordinate();
        $M = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }
}
