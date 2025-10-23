<?php

/**
 * The target class describing the moon.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;

/**
 * The target class describing the moon.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
class Moon extends Target
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(
            0.7275 * $this->calculateHorizontalMoonParallax() - 0.5666667
        );
    }

    /**
     * Calculates the horizontal moon parallax.
     *
     * To implement from chapter 30.
     *
     * @return float the horizontal moon parallax
     */
    public function calculateHorizontalMoonParallax(): float
    {
        return 0.950744559450172;
    }

    /**
     * Calculates the heliocentric coordinates of the moon.
     *
     * @param  Carbon  $date  The date
     * @return array L, B, R
     *
     * See chapter 47 of Astronomical Algorithms
     */
    public function calculateHeliocentricCoordinates(Carbon $date): array
    {
        // T = julian centuries since epoch J2000.0
        $T = (Time::getJd($date) - 2451545.0) / 36525.0;

        // Mean longitude of the moon
        $L_accent = (new Coordinate(218.3164477 + 481267.88123421 * $T - 0.0015786 * $T ** 2 + $T ** 3 / 538841.0 - $T ** 4 / 65194000.0))->getCoordinate();

        // Mean elongation of the moon
        $D = (new Coordinate(297.8501921 + 445267.1114023 * $T - 0.0018819 * $T ** 2 + $T ** 3 / 545868.0 - $T ** 4 / 113065000.0))->getCoordinate();

        // Sun's mean anomaly
        $M = (new Coordinate(357.5291092 + 35999.0502909 * $T - 0.0001536 * $T ** 2 + $T ** 3 / 24490000.0))->getCoordinate();

        // Moon's mean anomaly
        $M_accent = (new Coordinate(134.9633964 + 477198.8675055 * $T + 0.0087414 * $T ** 2 + $T ** 3 / 69699.0 - $T ** 4 / 14712000.0))->getCoordinate();

        // Moon's argument of latitude
        $F = (new Coordinate(93.2720950 + 483202.0175233 * $T - 0.0036539 * $T ** 2 - $T ** 3 / 3526000.0 + $T ** 4 / 863310000.0))->getCoordinate();

        $A1 = (new Coordinate(119.75 + 131.849 * $T))->getCoordinate();

        $A2 = (new Coordinate(53.09 + 479264.290 * $T))->getCoordinate();

        $A3 = (new Coordinate(313.45 + 481266.484 * $T))->getCoordinate();

        $E = 1 - 0.002516 * $T - 0.0000074 * $T ** 2;

        $sigmaL = 6288774.0 * sin(deg2rad($M_accent))
        + 1274027.0 * sin(deg2rad(2.0 * $D - $M_accent))
        + 658314.0 * sin(deg2rad(2.0 * $D))
        + 213618.0 * sin(deg2rad(2.0 * $M_accent))
        - 185116.0 * sin(deg2rad($M)) * $E
        - 114332.0 * sin(deg2rad(2.0 * $F))
        + 58793.0 * sin(deg2rad(2.0 * $D - 2.0 * $M_accent))
        + 57066.0 * sin(deg2rad(2.0 * $D - $M - $M_accent)) * $E
        + 53322.0 * sin(deg2rad(2.0 * $D + $M_accent))
        + 45758.0 * sin(deg2rad(2.0 * $D - $M)) * $E
        - 40923.0 * sin(deg2rad($M - $M_accent)) * $E
        - 34720.0 * sin(deg2rad($D))
        - 30383.0 * sin(deg2rad($M + $M_accent)) * $E
        + 15327.0 * sin(deg2rad(2.0 * $D - 2.0 * $F))
        - 12528.0 * sin(deg2rad($M_accent + 2.0 * $F))
        + 10980.0 * sin(deg2rad($M_accent - 2.0 * $F))
        + 10675.0 * sin(deg2rad(4.0 * $D - $M_accent))
        + 10034.0 * sin(deg2rad(3.0 * $M_accent))
        + 8548.0 * sin(deg2rad(4.0 * $D - 2.0 * $M_accent))
        - 7888.0 * sin(deg2rad(2.0 * $D + $M - $M_accent)) * $E
        - 6766.0 * sin(deg2rad(2.0 * $D + $M)) * $E
        - 5163.0 * sin(deg2rad($D - $M_accent))
        + 4987.0 * sin(deg2rad($D + $M)) * $E
        + 4036.0 * sin(deg2rad(2.0 * $D - $M + $M_accent)) * $E
        + 3994.0 * sin(deg2rad(2.0 * $D + 2.0 * $M_accent))
        + 3861.0 * sin(deg2rad(4.0 * $D))
        + 3665.0 * sin(deg2rad(2.0 * $D - 3.0 * $M_accent))
        - 2689.0 * sin(deg2rad($M - 2.0 * $M_accent)) * $E
        - 2602.0 * sin(deg2rad(2.0 * $D - $M_accent + 2.0 * $F))
        + 2390.0 * sin(deg2rad(2.0 * $D - $M - 2.0 * $M_accent)) * $E
        - 2348.0 * sin(deg2rad($D + $M_accent))
        + 2236.0 * sin(deg2rad(2.0 * $D - 2.0 * $M)) * pow($E, 2.0)
        - 2120.0 * sin(deg2rad($M + 2.0 * $M_accent)) * $E
        - 2069.0 * sin(deg2rad(2.0 * $M)) * pow($E, 2.0)
        + 2048.0 * sin(deg2rad(2.0 * $D - 2.0 * $M - $M_accent)) * pow($E, 2.0)
        - 1773.0 * sin(deg2rad(2.0 * $D + $M_accent - 2.0 * $F))
        - 1595.0 * sin(deg2rad(2.0 * $D + 2.0 * $F))
        + 1215.0 * sin(deg2rad(4.0 * $D - $M - $M_accent)) * $E
        - 1110.0 * sin(deg2rad(2.0 * $M_accent + 2.0 * $F))
         - 892.0 * sin(deg2rad(3.0 * $D - $M_accent))
         - 810.0 * sin(deg2rad(2.0 * $D + $M + $M_accent)) * $E
         + 759.0 * sin(deg2rad(4.0 * $D - $M - 2.0 * $M_accent)) * $E
         - 713.0 * sin(deg2rad(2.0 * $M - $M_accent)) * pow($E, 2.0)
         - 700.0 * sin(deg2rad(2.0 * $D + 2.0 * $M - $M_accent)) * pow($E, 2.0)
         + 691.0 * sin(deg2rad(2.0 * $D + $M - 2.0 * $M_accent)) * $E
         + 596.0 * sin(deg2rad(2.0 * $D - $M - 2.0 * $F)) * $E
         + 549.0 * sin(deg2rad(4.0 * $D + $M_accent))
         + 537.0 * sin(deg2rad(4.0 * $M_accent))
         + 520.0 * sin(deg2rad(4.0 * $D - $M)) * $E
         - 487.0 * sin(deg2rad($D - 2.0 * $M_accent))
         - 399.0 * sin(deg2rad(2.0 * $D + $M - 2.0 * $F)) * $E
         - 381.0 * sin(deg2rad(2.0 * $M_accent - 2.0 * $F))
         + 351.0 * sin(deg2rad($D + $M + $M_accent)) * $E
         - 340.0 * sin(deg2rad(3.0 * $D - 2.0 * $M_accent))
         + 330.0 * sin(deg2rad(4.0 * $D - 3.0 * $M_accent))
         + 327.0 * sin(deg2rad(2.0 * $D - $M + 2.0 * $M_accent)) * $E
         - 323.0 * sin(deg2rad(2.0 * $M + $M_accent)) * pow($E, 2.0)
         + 299.0 * sin(deg2rad($D + $M - $M_accent)) * $E
         + 294.0 * sin(deg2rad(2.0 * $D + 3.0 * $M_accent));

        $sigmaL += 3958.0 * sin(deg2rad($A1))
        + 1962.0 * sin(deg2rad($L_accent - $F))
        + 318.0 * sin(deg2rad($A2));

        $L = (new Coordinate($L_accent + $sigmaL / 1000000))->getCoordinate();

        $sigmaB = 5128122.0 * sin(deg2rad($F))
                + 280602.0 * sin(deg2rad($M_accent + $F))
                + 277693.0 * sin(deg2rad($M_accent - $F))
                + 173237.0 * sin(deg2rad(2.0 * $D - $F))
                + 55413.0 * sin(deg2rad(2.0 * $D - $M_accent + $F))
                + 46271.0 * sin(deg2rad(2.0 * $D - $M_accent - $F))
                + 32573.0 * sin(deg2rad(2.0 * $D + $F))
                + 17198.0 * sin(deg2rad(2.0 * $M_accent + $F))
                + 9266.0 * sin(deg2rad(2.0 * $D + $M_accent - $F))
                + 8822.0 * sin(deg2rad(2.0 * $M_accent - $F))
                + 8216.0 * sin(deg2rad(2.0 * $D - $M - $F)) * $E
                + 4324.0 * sin(deg2rad(2.0 * $D - 2.0 * $M_accent - $F))
                + 4200.0 * sin(deg2rad(2.0 * $D + $M_accent + $F))
                - 3359.0 * sin(deg2rad(2.0 * $D + $M - $F)) * $E
                + 2463.0 * sin(deg2rad(2.0 * $D - $M - $M_accent + $F)) * $E
                + 2211.0 * sin(deg2rad(2.0 * $D - $M + $F)) * $E
                + 2065.0 * sin(deg2rad(2.0 * $D - $M - $M_accent - $F)) * $E
                - 1870.0 * sin(deg2rad($M - $M_accent - $F)) * $E
                + 1828.0 * sin(deg2rad(4.0 * $D - $M_accent - $F))
                - 1794.0 * sin(deg2rad($M + $F)) * $E
                - 1749.0 * sin(deg2rad(3.0 * $F))
                - 1565.0 * sin(deg2rad($M - $M_accent + $F)) * $E
                - 1491.0 * sin(deg2rad($D + $F))
                - 1475.0 * sin(deg2rad($M + $M_accent + $F)) * $E
                - 1410.0 * sin(deg2rad($M + $M_accent - $F)) * $E
                - 1344.0 * sin(deg2rad($M - $F)) * $E
                - 1335.0 * sin(deg2rad($D - $F))
                + 1107.0 * sin(deg2rad(3.0 * $M_accent + $F))
                + 1021.0 * sin(deg2rad(4.0 * $D - $F))
                 + 833.0 * sin(deg2rad(4.0 * $D - $M_accent + $F))
                 + 777.0 * sin(deg2rad($M_accent - 3.0 * $F))
                 + 671.0 * sin(deg2rad(4.0 * $D - 2.0 * $M_accent + $F))
                 + 607.0 * sin(deg2rad(2.0 * $D - 3.0 * $F))
                 + 596.0 * sin(deg2rad(2.0 * $D + 2.0 * $M_accent - $F))
                 + 491.0 * sin(deg2rad(2.0 * $D - $M + $M_accent - $F)) * $E
                 - 451.0 * sin(deg2rad(2.0 * $D - 2.0 * $M_accent + $F))
                 + 439.0 * sin(deg2rad(3.0 * $M_accent - $F))
                 + 422.0 * sin(deg2rad(2.0 * $D + 2.0 * $M_accent + $F))
                 + 421.0 * sin(deg2rad(2.0 * $D - 3.0 * $M_accent - $F))
                 - 366.0 * sin(deg2rad(2.0 * $D + $M - $M_accent + $F)) * $E
                 - 351.0 * sin(deg2rad(2.0 * $D + $M + $F)) * $E
                 + 331.0 * sin(deg2rad(4.0 * $D + $F))
                 + 315.0 * sin(deg2rad(2.0 * $D - $M + $M_accent + $F)) * $E
                 + 302.0 * sin(deg2rad(2.0 * $D - 2.0 * $M - $F)) * pow($E, 2.0)
                 - 283.0 * sin(deg2rad($M_accent + 3.0 * $F))
                 - 229.0 * sin(deg2rad(2.0 * $D + $M + $M_accent - $F)) * $E
                 + 223.0 * sin(deg2rad($D + $M - $F)) * $E
                 + 223.0 * sin(deg2rad($D + $M + $F)) * $E
                 - 220.0 * sin(deg2rad($M - 2.0 * $M_accent - $F)) * $E
                 - 220.0 * sin(deg2rad(2.0 * $D + $M - $M_accent - $F)) * $E
                 - 185.0 * sin(deg2rad($D + $M_accent + $F))
                 + 181.0 * sin(deg2rad(2.0 * $D - $M - 2.0 * $M_accent - $F)) * $E
                 - 177.0 * sin(deg2rad($M + 2.0 * $M_accent + $F)) * $E
                 + 176.0 * sin(deg2rad(4.0 * $D - 2.0 * $M_accent - $F))
                 + 166.0 * sin(deg2rad(4.0 * $D - $M - $M_accent - $F)) * $E
                 - 164.0 * sin(deg2rad($D + $M_accent - $F))
                 + 132.0 * sin(deg2rad(4.0 * $D + $M_accent - $F))
                 - 119.0 * sin(deg2rad($D - $M_accent - $F))
                 + 115.0 * sin(deg2rad(4.0 * $D - $M - $F)) * $E
                 + 107.0 * sin(deg2rad(2.0 * $D - 2.0 * $M + $F)) * pow($E, 2.0);

        $sigmaB += -2235.0 * sin(deg2rad($L_accent))
                + 382.0 * sin(deg2rad($A3))
                + 175.0 * sin(deg2rad($A1 - $F))
                + 175.0 * sin(deg2rad($A1 + $F))
                + 127.0 * sin(deg2rad($L_accent - $M_accent))
                - 115.0 * sin(deg2rad($L_accent + $M_accent));

        $B = (new Coordinate($sigmaB / 1000000.0, -90, 90))->getCoordinate();

        $sigmaR = -20905355.0 * cos(deg2rad($M_accent))
                        - 3699111.0 * cos(deg2rad(2 * $D - $M_accent))
                        - 2955968.0 * cos(deg2rad(2 * $D))
                        - 569925.0 * cos(deg2rad(2 * $M_accent))
                        + 48888.0 * cos(deg2rad($M)) * $E
                        - 3149.0 * cos(deg2rad(2 * $F))
                        + 246158.0 * cos(deg2rad(2 * $D - 2 * $M_accent))
                        - 152138.0 * cos(deg2rad(2 * $D - $M - $M_accent)) * $E
                        - 170733.0 * cos(deg2rad(2 * $D + $M_accent))
                        - 204586.0 * cos(deg2rad(2 * $D - $M)) * $E
                        - 129620.0 * cos(deg2rad($M - $M_accent)) * $E
                        + 108743.0 * cos(deg2rad($D))
                        + 104755.0 * cos(deg2rad($M + $M_accent)) * $E
                        + 10321.0 * cos(deg2rad(2 * $D - 2 * $F))
                        + 79661.0 * cos(deg2rad($M_accent - 2 * $F))
                        - 34782.0 * cos(deg2rad(4 * $D - $M_accent))
                        - 23210.0 * cos(deg2rad(3 * $M_accent))
                        - 21636.0 * cos(deg2rad(4 * $D - 2 * $M_accent))
                        + 24208.0 * cos(deg2rad(2 * $D + $M - $M_accent)) * $E
                        + 30824.0 * cos(deg2rad(2 * $D + $M)) * $E
                        - 8379.0 * cos(deg2rad($D - $M_accent))
                        - 16675.0 * cos(deg2rad($D + $M)) * $E
                        - 12831.0 * cos(deg2rad(2 * $D - $M + $M_accent)) * $E
                        - 10445.0 * cos(deg2rad(2 * $D + 2 * $M_accent))
                        - 11650.0 * cos(deg2rad(4 * $D))
                        + 14403.0 * cos(deg2rad(2 * $D - 3 * $M_accent))
                        - 7003.0 * cos(deg2rad($M - 2 * $M_accent)) * $E
                        + 10056.0 * cos(deg2rad(2 * $D - $M - 2 * $M_accent)) * $E
                        + 6322.0 * cos(deg2rad($D + $M_accent))
                        - 9884.0 * cos(deg2rad(2 * $D - 2 * $M)) * pow($E, 2)
                        + 5751.0 * cos(deg2rad($M + 2 * $M_accent)) * $E
                        - 4950.0 * cos(deg2rad(2 * $D - 2 * $M - $M_accent)) * pow($E, 2)
                        + 4130.0 * cos(deg2rad(2 * $D + $M_accent - 2 * $F))
                        - 3958.0 * cos(deg2rad(4 * $D - $M - $M_accent)) * $E
                        + 3258.0 * cos(deg2rad(3 * $D - $M_accent))
                        + 2616.0 * cos(deg2rad(2 * $D + $M + $M_accent)) * $E
                        - 1897.0 * cos(deg2rad(4 * $D - $M - 2 * $M_accent)) * $E
                        - 2117.0 * cos(deg2rad(2 * $M - $M_accent)) * pow($E, 2)
                        + 2354.0 * cos(deg2rad(2 * $D + 2 * $M - $M_accent)) * pow($E, 2)
                        - 1423.0 * cos(deg2rad(4 * $D + $M_accent))
                        - 1117.0 * cos(deg2rad(4 * $M_accent))
                        - 1571.0 * cos(deg2rad(4 * $D - $M)) * $E
                        - 1739.0 * cos(deg2rad($D - 2 * $M_accent))
                        - 4421.0 * cos(deg2rad(2 * $M_accent - 2 * $F))
                        + 1165.0 * cos(deg2rad(2 * $M + $M_accent)) * pow($E, 2)
                        + 8752.0 * cos(deg2rad(2 * $D - $M_accent - 2 * $F));

        $R = round(385000.56 + $sigmaR / 1000, 1);

        return [$L, $B, $R];
    }

    /**
     * Calculates the apparent equatorial coordinates of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateApparentEquatorialCoordinates(Carbon $date, ...$args): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateApparentEquatorialCoordinates($date)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateApparentEquatorialCoordinates($date->addDay())
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateApparentEquatorialCoordinates($date->subDays(2))
        );
    }

    /**
     * Calculates the topocentric equatorial coordinates of the planet.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  GeographicalCoordinates  $geo_coords  The geographical coordinates
     * @param  float  $height  The height of the location
     *
     * See chapter 40 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // Accept variadic args for compatibility with the base Target signature.
        // Expected: [$geo_coords, $height]
        $geo_coords = $args[0] ?? null;
        $height = $args[1] ?? 0.0;

        if (! $geo_coords instanceof GeographicalCoordinates) {
            // Fallback: use a neutral geographical coordinate if none provided
            $geo_coords = new GeographicalCoordinates(0.0, 0.0);
        }

        $height = floatval($height);

        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $geo_coords, $height)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay(), $geo_coords, $height)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2), $geo_coords, $height)
        );
    }

    private function _calculateApparentEquatorialCoordinates(Carbon $date): EquatorialCoordinates
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $jd = Time::getJd($date);
        $pi = rad2deg(asin(6378.14 / $helio_coords[2]));
        $nutation = Time::nutation($jd);

        $helio_coords[0] += $nutation[0] / 3600.0;
        $ecl = new EclipticalCoordinates($helio_coords[0], $helio_coords[1]);

        return $ecl->convertToEquatorial($nutation[3]);
    }

    private function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): EquatorialCoordinates
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $jd = Time::getJd($date);
        $nutation = Time::nutation($jd);

        $helio_coords[0] += $nutation[0] / 3600.0;
        $ecl = new EclipticalCoordinates($helio_coords[0], $helio_coords[1]);

        $equa_coords = $ecl->convertToEquatorial($nutation[3]);

        // Calculate corrections for parallax
        $pi = rad2deg(asin(6378.14 / $helio_coords[2]));

        $siderial_time = Time::apparentSiderialTime($date, new GeographicalCoordinates(0.0, 0.0));

        $hour_angle = (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($equa_coords->getHourAngle($siderial_time) + $geo_coords->getLongitude()->getCoordinate() * 15.0, 0, 360))->getCoordinate();

        $earthsGlobe = $geo_coords->earthsGlobe($height);

        $deltara = rad2deg(atan(-$earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)) / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)))));
        $dec = rad2deg(atan((sin(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[0] * sin(deg2rad($pi / 3600.0))) * cos(deg2rad($deltara / 3600.0))
                        / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * cos(deg2rad($height)))));

        $equa_coords->setRA($equa_coords->getRA()->getCoordinate() + $deltara);
        $equa_coords->setDeclination($dec);

        return $equa_coords;
    }

    /**
     * Calculates the illuminated fraction of the moon.
     *
     * @param  Carbon  $date  The date for which to calculate the fraction
     * @return float The illuminated fraction, the phase ratio
     *
     * See chapter 48 of Astronomical Algorithms
     */
    public function illuminatedFraction(Carbon $date): float
    {
        $moonCoords = $this->_calculateApparentEquatorialCoordinates($date);
        $delta = $this->calculateHeliocentricCoordinates($date)[2];

        $sun = new Sun();
        $nutation = Time::nutation(Time::getJd($date));
        $sun->calculateEquatorialCoordinatesHighAccuracy($date, $nutation);
        $sunCoords = $sun->getEquatorialCoordinatesToday();

        $earth = new Earth();
        $R = $earth->calculateHeliocentricCoordinates($date)[2] * 149598073;

        $cosPsi = sin(deg2rad($sunCoords->getDeclination()->getCoordinate())) * sin(deg2rad($moonCoords->getDeclination()->getCoordinate()))
            + cos(deg2rad($sunCoords->getDeclination()->getCoordinate())) * cos(deg2rad($moonCoords->getDeclination()->getCoordinate())) * cos(deg2rad($sunCoords->getRA()->getCoordinate() * 15 - $moonCoords->getRA()->getCoordinate() * 15));
        $psi = acos($cosPsi);
        $i = rad2deg(atan2($R * sin($psi), $delta - $R * $cosPsi));
        $i = $i - floor($i / 360.0) * 360.0;

        return round((1 + cos(deg2rad($i))) / 2, 3);
    }

    /**
     * Calculates the phase ration of the moon (0 - 1), where 0=new, 0.5=full, 1=new.
     *
     * @param  Carbon  $date  The date for which to calculate the phase ration
     * @return float The phase ratio
     *
     * See chapter 49 of Astronomical Algorithms
     */
    public function getPhaseRatio(Carbon $date): float
    {
        $nextNewMoon = $this->newMoonDate($date);
        $prevMonth = $nextNewMoon->copy()->subDays(32);
        $previousNewMoon = $this->newMoonDate($prevMonth);

        $lunation = (Time::getJd($date) - Time::getJd($previousNewMoon)) / (Time::getJd($nextNewMoon) - Time::getJd($previousNewMoon));

        return $lunation;
    }

    /**
     * Return the date for the new moon after the given date.
     *
     * @param  Carbon  $date  The date after which we search the new moon
     * @return Carbon The date of the new moon after the given date.
     */
    public function newMoonDate(Carbon $date): Carbon
    {
        $k = (($date->year + $date->dayOfYear / $date->daysInYear - 2000) * 12.3685);
        if ($k > 0) {
            $k = floor($k);
        } else {
            $k = ceil($k);
        }
        $T = $k / 1236.85;
        $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;

        if ($JDE < Time::getJd($date)) {
            $k++;
            $T = $k / 1236.85;
            $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        }

        $E = 1 - 0.002516 * $T - 0.0000074 * $T ** 2;
        $M = (new Coordinate(2.5534 + 29.10535670 * $k - 0.0000014 * $T ** 2 - 0.00000011 * $T ** 3, 0, 360))->getCoordinate();
        $M_accent = (new Coordinate(201.5643 + 385.81693528 * $k + 0.0107582 * $T ** 2 + 0.00001238 * $T ** 3 - 0.000000058 * $T ** 4, 0, 360))->getCoordinate();
        $F = (new Coordinate(160.7108 + 390.67050284 * $k - 0.0016118 * $T ** 2 - 0.00000227 * $T ** 3 + 0.000000011 * $T ** 4, 0, 360))->getCoordinate();
        $omega = (new Coordinate(124.7746 - 1.56375588 * $k + 0.0020672 * $T ** 2 + 0.00000215 * $T ** 3, 0, 360))->getCoordinate();

        $corr1 = -0.40720 * sin(deg2rad($M_accent))
                + 0.17241 * $E * sin(deg2rad($M))
                + 0.01608 * sin(deg2rad(2 * $M_accent))
                + 0.01039 * sin(deg2rad(2 * $F))
                + 0.00739 * $E * sin(deg2rad($M_accent - $M))
                - 0.00514 * $E * sin(deg2rad($M_accent + $M))
                + 0.00208 * $E ** 2 * sin(deg2rad(2 * $M))
                - 0.00111 * sin(deg2rad($M_accent - 2 * $F))
                - 0.00057 * sin(deg2rad($M_accent + 2 * $F))
                + 0.00056 * $E * sin(deg2rad(2 * $M_accent + $M))
                - 0.00042 * sin(deg2rad(3 * $M_accent))
                + 0.00042 * $E * sin(deg2rad($M + 2 * $F))
                + 0.00038 * $E * sin(deg2rad($M - 2 * $F))
                - 0.00024 * $E * sin(deg2rad(2 * $M_accent - $M))
                - 0.00017 * sin(deg2rad($omega))
                - 0.00007 * sin(deg2rad($M_accent + 2 * $M))
                + 0.00004 * sin(deg2rad(2 * $M_accent - 2 * $F))
                + 0.00004 * sin(deg2rad(3 * $M))
                + 0.00003 * sin(deg2rad($M_accent + $M - 2 * $F))
                + 0.00003 * sin(deg2rad(2 * $M_accent + 2 * $F))
                - 0.00003 * sin(deg2rad($M_accent + $M + 2 * $F))
                + 0.00003 * sin(deg2rad($M_accent - $M + 2 * $F))
                - 0.00002 * sin(deg2rad($M_accent - $M - 2 * $F))
                - 0.00002 * sin(deg2rad(3 * $M_accent + $M))
                + 0.00002 * sin(deg2rad(4 * $M_accent));

        $A1 = deg2rad(299.77 + 0.107408 * $k - 0.009173 * $T ** 2);
        $A2 = deg2rad(251.88 + 0.016321 * $k);
        $A3 = deg2rad(251.83 + 26.651886 * $k);
        $A4 = deg2rad(349.42 + 36.412478 * $k);
        $A5 = deg2rad(84.66 + 18.206239 * $k);
        $A6 = deg2rad(141.74 + 53.303771 * $k);
        $A7 = deg2rad(207.14 + 2.453732 * $k);
        $A8 = deg2rad(154.84 + 7.306860 * $k);
        $A9 = deg2rad(34.52 + 27.261239 * $k);
        $A10 = deg2rad(207.19 + 0.121824 * $k);
        $A11 = deg2rad(291.34 + 1.844379 * $k);
        $A12 = deg2rad(161.72 + 24.198154 * $k);
        $A13 = deg2rad(239.56 + 25.513099 * $k);
        $A14 = deg2rad(331.55 + 3.592518 * $k);

        $corr2 = 0.000325 * sin($A1)
                 + 0.000165 * sin($A2)
                 + 0.000164 * sin($A3)
                 + 0.000126 * sin($A4)
                 + 0.000110 * sin($A5)
                 + 0.000062 * sin($A6)
                 + 0.000060 * sin($A7)
                 + 0.000056 * sin($A8)
                 + 0.000047 * sin($A9)
                 + 0.000042 * sin($A10)
                 + 0.000040 * sin($A11)
                 + 0.000037 * sin($A12)
                 + 0.000035 * sin($A13)
                 + 0.000023 * sin($A14);

        $JDE = $JDE + $corr1 + $corr2;

        return Time::fromJd($JDE);
    }

    /**
     * Return the date for the full moon after the given date.
     *
     * @param  Carbon  $date  The date after which we search the full moon
     * @return Carbon The date of the full moon after the given date.
     */
    public function fullMoonDate(Carbon $date): Carbon
    {
        $k = (($date->year + $date->dayOfYear / $date->daysInYear - 2000) * 12.3685);
        if ($k < 0) {
            $k = round($k) - 0.5;
        } else {
            $k = round($k) + 0.5;
        }
        $T = $k / 1236.85;
        $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        if ($JDE < Time::getJd($date)) {
            $k++;
            $T = $k / 1236.85;
            $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        }

        $E = 1 - 0.002516 * $T - 0.0000074 * $T ** 2;
        $M = (new Coordinate(2.5534 + 29.10535670 * $k - 0.0000014 * $T ** 2 - 0.00000011 * $T ** 3, 0, 360))->getCoordinate();
        $M_accent = (new Coordinate(201.5643 + 385.81693528 * $k + 0.0107582 * $T ** 2 + 0.00001238 * $T ** 3 - 0.000000058 * $T ** 4, 0, 360))->getCoordinate();
        $F = (new Coordinate(160.7108 + 390.67050284 * $k - 0.0016118 * $T ** 2 - 0.00000227 * $T ** 3 + 0.000000011 * $T ** 4, 0, 360))->getCoordinate();
        $omega = (new Coordinate(124.7746 - 1.56375588 * $k + 0.0020672 * $T ** 2 + 0.00000215 * $T ** 3, 0, 360))->getCoordinate();

        $corr1 = -0.40614 * sin(deg2rad($M_accent))
                + 0.17302 * $E * sin(deg2rad($M))
                + 0.01614 * sin(deg2rad(2 * $M_accent))
                + 0.01043 * sin(deg2rad(2 * $F))
                + 0.00734 * $E * sin(deg2rad($M_accent - $M))
                - 0.00515 * $E * sin(deg2rad($M_accent + $M))
                + 0.00209 * $E ** 2 * sin(deg2rad(2 * $M))
                - 0.00111 * sin(deg2rad($M_accent - 2 * $F))
                - 0.00057 * sin(deg2rad($M_accent + 2 * $F))
                + 0.00056 * $E * sin(deg2rad(2 * $M_accent + $M))
                - 0.00042 * sin(deg2rad(3 * $M_accent))
                + 0.00042 * $E * sin(deg2rad($M + 2 * $F))
                + 0.00038 * $E * sin(deg2rad($M - 2 * $F))
                - 0.00024 * $E * sin(deg2rad(2 * $M_accent - $M))
                - 0.00017 * sin(deg2rad($omega))
                - 0.00007 * sin(deg2rad($M_accent + 2 * $M))
                + 0.00004 * sin(deg2rad(2 * $M_accent - 2 * $F))
                + 0.00004 * sin(deg2rad(3 * $M))
                + 0.00003 * sin(deg2rad($M_accent + $M - 2 * $F))
                + 0.00003 * sin(deg2rad(2 * $M_accent + 2 * $F))
                - 0.00003 * sin(deg2rad($M_accent + $M + 2 * $F))
                + 0.00003 * sin(deg2rad($M_accent - $M + 2 * $F))
                - 0.00002 * sin(deg2rad($M_accent - $M - 2 * $F))
                - 0.00002 * sin(deg2rad(3 * $M_accent + $M))
                + 0.00002 * sin(deg2rad(4 * $M_accent));

        $A1 = deg2rad(299.77 + 0.107408 * $k - 0.009173 * $T ** 2);
        $A2 = deg2rad(251.88 + 0.016321 * $k);
        $A3 = deg2rad(251.83 + 26.651886 * $k);
        $A4 = deg2rad(349.42 + 36.412478 * $k);
        $A5 = deg2rad(84.66 + 18.206239 * $k);
        $A6 = deg2rad(141.74 + 53.303771 * $k);
        $A7 = deg2rad(207.14 + 2.453732 * $k);
        $A8 = deg2rad(154.84 + 7.306860 * $k);
        $A9 = deg2rad(34.52 + 27.261239 * $k);
        $A10 = deg2rad(207.19 + 0.121824 * $k);
        $A11 = deg2rad(291.34 + 1.844379 * $k);
        $A12 = deg2rad(161.72 + 24.198154 * $k);
        $A13 = deg2rad(239.56 + 25.513099 * $k);
        $A14 = deg2rad(331.55 + 3.592518 * $k);

        $corr2 = 0.000325 * sin($A1)
                 + 0.000165 * sin($A2)
                 + 0.000164 * sin($A3)
                 + 0.000126 * sin($A4)
                 + 0.000110 * sin($A5)
                 + 0.000062 * sin($A6)
                 + 0.000060 * sin($A7)
                 + 0.000056 * sin($A8)
                 + 0.000047 * sin($A9)
                 + 0.000042 * sin($A10)
                 + 0.000040 * sin($A11)
                 + 0.000037 * sin($A12)
                 + 0.000035 * sin($A13)
                 + 0.000023 * sin($A14);

        $JDE = $JDE + $corr1 + $corr2;

        return Time::fromJd($JDE);
    }

    /**
     * Return the date for the first quarter moon after the given date.
     *
     * @param  Carbon  $date  The date after which we search the first quarter moon
     * @return Carbon The date of the first quarter moon after the given date.
     */
    public function firstQuarterMoonDate(Carbon $date): Carbon
    {
        $k = (($date->year + $date->dayOfYear / $date->daysInYear - 2000) * 12.3685);
        if ($k < 0) {
            $k = round($k + 0.25) - 0.25;
        } else {
            $k = round($k + 0.25) + 0.25;
        }
        $T = $k / 1236.85;
        $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        if ($JDE < Time::getJd($date)) {
            $k++;
            $T = $k / 1236.85;
            $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        }

        $E = 1 - 0.002516 * $T - 0.0000074 * $T ** 2;
        $M = (new Coordinate(2.5534 + 29.10535670 * $k - 0.0000014 * $T ** 2 - 0.00000011 * $T ** 3, 0, 360))->getCoordinate();
        $M_accent = (new Coordinate(201.5643 + 385.81693528 * $k + 0.0107582 * $T ** 2 + 0.00001238 * $T ** 3 - 0.000000058 * $T ** 4, 0, 360))->getCoordinate();
        $F = (new Coordinate(160.7108 + 390.67050284 * $k - 0.0016118 * $T ** 2 - 0.00000227 * $T ** 3 + 0.000000011 * $T ** 4, 0, 360))->getCoordinate();
        $omega = (new Coordinate(124.7746 - 1.56375588 * $k + 0.0020672 * $T ** 2 + 0.00000215 * $T ** 3, 0, 360))->getCoordinate();

        $corr1 = -0.62801 * sin(deg2rad($M_accent))
                + 0.17172 * $E * sin(deg2rad($M))
                - 0.01183 * $E * sin(deg2rad($M_accent + $M))
                + 0.00862 * sin(deg2rad(2 * $M_accent))
                + 0.00804 * sin(deg2rad(2 * $F))
                + 0.00454 * $E * sin(deg2rad($M_accent - $M))
                + 0.00204 * $E ** 2 * sin(deg2rad(2 * $M))
                - 0.00180 * sin(deg2rad($M_accent - 2 * $F))
                - 0.00070 * sin(deg2rad($M_accent + 2 * $F))
                - 0.00040 * $E * sin(deg2rad(3 * $M_accent))
                - 0.00034 * $E * sin(deg2rad(2 * $M_accent - $M))
                + 0.00032 * $E * sin(deg2rad($M + 2 * $F))
                + 0.00032 * $E * sin(deg2rad($M - 2 * $F))
                - 0.00028 * $E * sin(deg2rad($M_accent + 2 * $M))
                + 0.00027 * $E * sin(deg2rad(2 * $M_accent + $M))
                - 0.00017 * sin(deg2rad($omega))
                - 0.00005 * sin(deg2rad($M_accent - $M - 2 * $F))
                + 0.00004 * sin(deg2rad(2 * $M_accent + 2 * $F))
                - 0.00004 * sin(deg2rad($M_accent + $M + 2 * $F))
                + 0.00004 * sin(deg2rad($M_accent - 2 * $M))
                + 0.00003 * sin(deg2rad($M_accent + $M - 2 * $F))
                + 0.00003 * sin(deg2rad(3 * $M))
                + 0.00002 * sin(deg2rad(2 * $M_accent - 2 * $F))
                + 0.00002 * sin(deg2rad($M_accent - $M + 2 * $F))
                - 0.00002 * sin(deg2rad(3 * $M_accent + $M));

        $W = 0.00306 - 0.00038 * $E * cos(deg2rad($M)) + 0.00026 * cos(deg2rad($M_accent)) - 0.00002 * cos(deg2rad($M_accent - $M)) + 0.00002 * cos(deg2rad($M_accent + $M)) + 0.00002 * cos(deg2rad(2 * $F));

        $A1 = deg2rad(299.77 + 0.107408 * $k - 0.009173 * $T ** 2);
        $A2 = deg2rad(251.88 + 0.016321 * $k);
        $A3 = deg2rad(251.83 + 26.651886 * $k);
        $A4 = deg2rad(349.42 + 36.412478 * $k);
        $A5 = deg2rad(84.66 + 18.206239 * $k);
        $A6 = deg2rad(141.74 + 53.303771 * $k);
        $A7 = deg2rad(207.14 + 2.453732 * $k);
        $A8 = deg2rad(154.84 + 7.306860 * $k);
        $A9 = deg2rad(34.52 + 27.261239 * $k);
        $A10 = deg2rad(207.19 + 0.121824 * $k);
        $A11 = deg2rad(291.34 + 1.844379 * $k);
        $A12 = deg2rad(161.72 + 24.198154 * $k);
        $A13 = deg2rad(239.56 + 25.513099 * $k);
        $A14 = deg2rad(331.55 + 3.592518 * $k);

        $corr2 = 0.000325 * sin($A1)
                 + 0.000165 * sin($A2)
                 + 0.000164 * sin($A3)
                 + 0.000126 * sin($A4)
                 + 0.000110 * sin($A5)
                 + 0.000062 * sin($A6)
                 + 0.000060 * sin($A7)
                 + 0.000056 * sin($A8)
                 + 0.000047 * sin($A9)
                 + 0.000042 * sin($A10)
                 + 0.000040 * sin($A11)
                 + 0.000037 * sin($A12)
                 + 0.000035 * sin($A13)
                 + 0.000023 * sin($A14);

        $JDE = $JDE + $corr1 + $corr2 + $W;

        return Time::fromJd($JDE);
    }

    /**
     * Return the date for the last quarter moon after the given date.
     *
     * @param  Carbon  $date  The date after which we search the last quarter moon
     * @return Carbon The date of the last quarter moon after the given date.
     */
    public function lastQuarterMoonDate(Carbon $date): Carbon
    {
        $k = (($date->year + $date->dayOfYear / $date->daysInYear - 2000) * 12.3685);
        if ($k < 0) {
            $k = round($k - 0.25) - 0.75;
        } else {
            $k = round($k - 0.25) + 0.75;
        }
        $T = $k / 1236.85;
        $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        if ($JDE < Time::getJd($date)) {
            $k++;
            $T = $k / 1236.85;
            $JDE = 2451550.09766 + 29.530588861 * $k + 0.00015437 * $T ** 2 - 0.000000150 * $T ** 3 + 0.00000000073 * $T ** 4;
        }

        $E = 1 - 0.002516 * $T - 0.0000074 * $T ** 2;
        $M = (new Coordinate(2.5534 + 29.10535670 * $k - 0.0000014 * $T ** 2 - 0.00000011 * $T ** 3, 0, 360))->getCoordinate();
        $M_accent = (new Coordinate(201.5643 + 385.81693528 * $k + 0.0107582 * $T ** 2 + 0.00001238 * $T ** 3 - 0.000000058 * $T ** 4, 0, 360))->getCoordinate();
        $F = (new Coordinate(160.7108 + 390.67050284 * $k - 0.0016118 * $T ** 2 - 0.00000227 * $T ** 3 + 0.000000011 * $T ** 4, 0, 360))->getCoordinate();
        $omega = (new Coordinate(124.7746 - 1.56375588 * $k + 0.0020672 * $T ** 2 + 0.00000215 * $T ** 3, 0, 360))->getCoordinate();

        $corr1 = -0.62801 * sin(deg2rad($M_accent))
                + 0.17172 * $E * sin(deg2rad($M))
                - 0.01183 * $E * sin(deg2rad($M_accent + $M))
                + 0.00862 * sin(deg2rad(2 * $M_accent))
                + 0.00804 * sin(deg2rad(2 * $F))
                + 0.00454 * $E * sin(deg2rad($M_accent - $M))
                + 0.00204 * $E ** 2 * sin(deg2rad(2 * $M))
                - 0.00180 * sin(deg2rad($M_accent - 2 * $F))
                - 0.00070 * sin(deg2rad($M_accent + 2 * $F))
                - 0.00040 * $E * sin(deg2rad(3 * $M_accent))
                - 0.00034 * $E * sin(deg2rad(2 * $M_accent - $M))
                + 0.00032 * $E * sin(deg2rad($M + 2 * $F))
                + 0.00032 * $E * sin(deg2rad($M - 2 * $F))
                - 0.00028 * $E * sin(deg2rad($M_accent + 2 * $M))
                + 0.00027 * $E * sin(deg2rad(2 * $M_accent + $M))
                - 0.00017 * sin(deg2rad($omega))
                - 0.00005 * sin(deg2rad($M_accent - $M - 2 * $F))
                + 0.00004 * sin(deg2rad(2 * $M_accent + 2 * $F))
                - 0.00004 * sin(deg2rad($M_accent + $M + 2 * $F))
                + 0.00004 * sin(deg2rad($M_accent - 2 * $M))
                + 0.00003 * sin(deg2rad($M_accent + $M - 2 * $F))
                + 0.00003 * sin(deg2rad(3 * $M))
                + 0.00002 * sin(deg2rad(2 * $M_accent - 2 * $F))
                + 0.00002 * sin(deg2rad($M_accent - $M + 2 * $F))
                - 0.00002 * sin(deg2rad(3 * $M_accent + $M));

        $W = 0.00306 - 0.00038 * $E * cos(deg2rad($M)) + 0.00026 * cos(deg2rad($M_accent)) - 0.00002 * cos(deg2rad($M_accent - $M)) + 0.00002 * cos(deg2rad($M_accent + $M)) + 0.00002 * cos(deg2rad(2 * $F));

        $A1 = deg2rad(299.77 + 0.107408 * $k - 0.009173 * $T ** 2);
        $A2 = deg2rad(251.88 + 0.016321 * $k);
        $A3 = deg2rad(251.83 + 26.651886 * $k);
        $A4 = deg2rad(349.42 + 36.412478 * $k);
        $A5 = deg2rad(84.66 + 18.206239 * $k);
        $A6 = deg2rad(141.74 + 53.303771 * $k);
        $A7 = deg2rad(207.14 + 2.453732 * $k);
        $A8 = deg2rad(154.84 + 7.306860 * $k);
        $A9 = deg2rad(34.52 + 27.261239 * $k);
        $A10 = deg2rad(207.19 + 0.121824 * $k);
        $A11 = deg2rad(291.34 + 1.844379 * $k);
        $A12 = deg2rad(161.72 + 24.198154 * $k);
        $A13 = deg2rad(239.56 + 25.513099 * $k);
        $A14 = deg2rad(331.55 + 3.592518 * $k);

        $corr2 = 0.000325 * sin($A1)
                 + 0.000165 * sin($A2)
                 + 0.000164 * sin($A3)
                 + 0.000126 * sin($A4)
                 + 0.000110 * sin($A5)
                 + 0.000062 * sin($A6)
                 + 0.000060 * sin($A7)
                 + 0.000056 * sin($A8)
                 + 0.000047 * sin($A9)
                 + 0.000042 * sin($A10)
                 + 0.000040 * sin($A11)
                 + 0.000037 * sin($A12)
                 + 0.000035 * sin($A13)
                 + 0.000023 * sin($A14);

        $JDE = $JDE + $corr1 + $corr2 - $W;

        return Time::fromJd($JDE);
    }

    /**
     * Calculate the diameter of the Moon.  You can get the diamter
     * by using the getDiameter method.
     *
     * @param  Carbon  $date  The date
     * @return None
     *
     * Chapter 55 of Astronomical Algorithms
     */
    public function calculateDiameter(Carbon $date)
    {
        $distance = $this->calculateHeliocentricCoordinates($date)[2];

        $this->setDiameter(round(2 * 358473400 / $distance, 1));
    }
}
