<?php

/**
 * The target class describing the moon.
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
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * The target class describing the moon.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
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
     * @param Carbon $date The date
     *
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
     * @param Carbon $date      The date for which to calculate the coordinates
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateApparentEquatorialCoordinates(Carbon $date): void
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
     * @param Carbon                  $date       The date for which to calculate the coordinates
     * @param GeographicalCoordinates $geo_coords The geographical coordinates
     * @param float                   $height     The height of the location
     *
     * See chapter 40 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): void
    {
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
        $jd           = Time::getJd($date);
        $pi           = rad2deg(asin(6378.14 / $helio_coords[2]));
        $nutation     = Time::nutation($jd);

        $helio_coords[0] += $nutation[0] / 3600.0;
        $ecl = new EclipticalCoordinates($helio_coords[0], $helio_coords[1]);

        return $ecl->convertToEquatorial($nutation[3]);
    }

    private function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): EquatorialCoordinates
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $jd           = Time::getJd($date);
        $nutation     = Time::nutation($jd);

        $helio_coords[0] += $nutation[0] / 3600.0;
        $ecl = new EclipticalCoordinates($helio_coords[0], $helio_coords[1]);

        $equa_coords = $ecl->convertToEquatorial($nutation[3]);

        // Calculate corrections for parallax
        $pi = rad2deg(asin(6378.14 / $helio_coords[2]));

        $siderial_time = Time::apparentSiderialTime($date, new GeographicalCoordinates(0.0, 0.0));

        $hour_angle = (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($equa_coords->getHourAngle($siderial_time) + $geo_coords->getLongitude()->getCoordinate() * 15.0, 0, 360))->getCoordinate();

        $earthsGlobe = $geo_coords->earthsGlobe($height);

        $deltara = rad2deg(atan(-$earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)) / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)))));
        $dec     = rad2deg(atan((sin(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[0] * sin(deg2rad($pi / 3600.0))) * cos(deg2rad($deltara / 3600.0))
                        / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * cos(deg2rad($height)))));

        $equa_coords->setRA($equa_coords->getRA()->getCoordinate() + $deltara);
        $equa_coords->setDeclination($dec);

        return $equa_coords;
    }

    /**
     * Calculates the illuminated fraction of the planet.
     *
     * @param Carbon $date The date for which to calculate the fraction
     *
     * @return float The illuminated fraction
     *
     * See chapter 58 of Astronomical Algorithms
     */
    public function illuminatedFraction(Carbon $date): float
    {
        // T = julian centuries since epoch J2000.0
        $T = (Time::getJd($date) - 2451545.0) / 36525.0;

        // Mean elongation of the moon
        $D = (new Coordinate(297.8501921 + 445267.1114023 * $T - 0.0018819 * $T ** 2 + $T ** 3 / 545868.0 - $T ** 4 / 113065000.0))->getCoordinate();

        // Sun's mean anomaly
        $M = (new Coordinate(357.5291092 + 35999.0502909 * $T - 0.0001536 * $T ** 2 + $T ** 3 / 24490000.0))->getCoordinate();

        // Moon's mean anomaly
        $M_accent = (new Coordinate(134.9633964 + 477198.8675055 * $T + 0.0087414 * $T ** 2 + $T ** 3 / 69699.0 - $T ** 4 / 14712000.0))->getCoordinate();

        $i = 180 - $D - 6.289 * sin(deg2rad($M_accent))
                                     + 2.100 * sin(deg2rad($M))
                                     - 1.274 * sin(deg2rad(2 * $D - $M_accent))
                                     - 0.658 * sin(deg2rad(2 * $D))
                                     - 0.214 * sin(deg2rad(2 * $M_accent))
                                     - 0.110 * sin(deg2rad($D));

        $i = $i - floor($i / 360.0) * 360.0;

        return (1 + cos(deg2rad($i))) / 2;
    }
}
