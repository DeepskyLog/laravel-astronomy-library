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
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;

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
        $T  = ($jd - 2451545.0) / 36525.0;

        $L     = (new Coordinate(252.250906 + 149474.0722491 * $T + 0.00030350 * $T ** 2 + 0.000000018 * $T ** 3, 0, 360))->getCoordinate();
        $a     = 0.387098310;
        $e     = 0.20563175 + 0.000020407 * $T - 0.0000000283 * $T ** 2 - 0.00000000018 * $T ** 3;
        $i     = (new Coordinate(7.004986 + 0.0018215 * $T - 0.00001810 * $T ** 2 + 0.000000056 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(48.330893 + 1.1861883 * $T + 0.00017542 * $T ** 2 + 0.000000215 * $T ** 3, 0, 360))->getCoordinate();
        $pi    = (new Coordinate(77.456119 + 1.5564776 * $T + 0.00029544 * $T ** 2 + 0.000000009 * $T ** 3, 0, 360))->getCoordinate();
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
        $jd = Time::getJd($date);
        $T  = ($jd - 2451545.0) / 36525.0;

        $L     = (new Coordinate(252.250906 + 149472.6746358 * $T - 0.00000536 * $T ** 2 + 0.000000002 * $T ** 3, 0, 360))->getCoordinate();
        $a     = 0.387098310;
        $e     = 0.20563175 + 0.000020407 * $T - 0.0000000283 * $T ** 2 - 0.00000000018 * $T ** 3;
        $i     = (new Coordinate(7.004986 - 0.0059516 * $T + 0.00000080 * $T ** 2 + 0.000000043 * $T ** 3, 0, 360))->getCoordinate();
        $omega = (new Coordinate(48.330893 - 0.1254227 * $T - 0.00008833 * $T ** 2 - 0.000000200 * $T ** 3, 0, 360))->getCoordinate();
        $pi    = (new Coordinate(77.456119 + 0.1588643 * $T - 0.00001342 * $T ** 2 - 0.000000007 * $T ** 3, 0, 360))->getCoordinate();
        $M     = $L - $pi;

        return [$L, $a, $e, $i, $omega, $pi, $M];
    }

    /**
     * Calculates the heliocentric coordinates of Mercury.
     *
     * @param Carbon $date The date
     *
     * @return array L, B, R
     *
     * See chapter 32 of Astronomical Algorithms
     */
    public function calculateHeliocentricCoordinates(Carbon $date): array
    {
        // tau = julian millenia since epoch J2000.0
        $tau = (Time::getJd($date) - 2451545.0) / 365250.0;

        $L0 = 440250710.0 * cos(0.0)
            + 40989415.0 * cos(1.48302034 + 26087.90314157 * $tau)
            + 5046294.0 * cos(4.4778549 + 52175.8062831 * $tau)
            + 855347.0 * cos(1.165203 + 78263.709425 * $tau)
            + 165590.0 * cos(4.119692 + 104351.612566 * $tau)
            + 34562.0 * cos(0.77931 + 130439.51571 * $tau)
            + 7583.0 * cos(3.7135 + 156527.4188 * $tau)
            + 3560.0 * cos(1.5120 + 1109.3786 * $tau)
            + 1803.0 * cos(4.1033 + 5661.3320 * $tau)
            + 1726.0 * cos(0.3583 + 182615.3220 * $tau)
            + 1590.0 * cos(2.9951 + 25028.5212 * $tau)
            + 1365.0 * cos(4.5992 + 27197.2817 * $tau)
            + 1017.0 * cos(0.8803 + 31749.2352 * $tau)
            + 714.0 * cos(1.541 + 24978.525 * $tau)
            + 644.0 * cos(5.303 + 21535.950 * $tau)
            + 451.0 * cos(6.050 + 51116.424 * $tau)
            + 404.0 * cos(3.282 + 208703.225 * $tau)
            + 352.0 * cos(5.242 + 20426.571 * $tau)
            + 345.0 * cos(2.792 + 15874.618 * $tau)
            + 343.0 * cos(5.765 + 955.600 * $tau)
            + 339.0 * cos(5.863 + 25558.212 * $tau)
            + 325.0 * cos(1.337 + 53285.185 * $tau)
            + 273.0 * cos(2.495 + 529.691 * $tau)
            + 264.0 * cos(3.917 + 57837.138 * $tau)
            + 260.0 * cos(0.987 + 4551.953 * $tau)
            + 239.0 * cos(0.113 + 1059.382 * $tau)
            + 235.0 * cos(0.267 + 11322.664 * $tau)
            + 217.0 * cos(0.660 + 13521.751 * $tau)
            + 209.0 * cos(2.092 + 47623.853 * $tau)
            + 183.0 * cos(2.629 + 27043.503 * $tau)
            + 182.0 * cos(2.434 + 25661.305 * $tau)
            + 176.0 * cos(4.536 + 51066.428 * $tau)
            + 173.0 * cos(2.452 + 24498.830 * $tau)
            + 142.0 * cos(3.360 + 37410.567 * $tau)
            + 138.0 * cos(0.291 + 10213.286 * $tau)
            + 125.0 * cos(3.721 + 39609.655 * $tau)
            + 118.0 * cos(2.781 + 77204.327 * $tau)
            + 106.0 * cos(4.206 + 19804.827 * $tau);

        $L1 = (2608814706223.0 * cos(0.0)
                + 1126008.0 * cos(6.2170397 + 26087.9031416 * $tau)
                + 303471.0 * cos(3.055655 + 52175.806283 * $tau)
                + 80538.0 * cos(6.10455 + 78263.70942 * $tau)
                + 21245.0 * cos(2.83532 + 104351.61257 * $tau)
                + 5592.0 * cos(5.8268 + 130439.5157 * $tau)
                + 1472.0 * cos(2.5185 + 156527.4188 * $tau)
                + 388.0 * cos(5.480 + 182615.322 * $tau)
                + 352.0 * cos(3.052 + 1109.379 * $tau)
                + 103.0 * cos(2.149 + 208703.225 * $tau)
                + 94.0 * cos(6.12 + 27197.28 * $tau)
                + 91.0 * cos(24978.52 * $tau)
                + 52.0 * cos(5.62 + 5661.33 * $tau)
                + 44.0 * cos(4.57 + 25028.52 * $tau)
                + 28.0 * cos(3.04 + 51066.43 * $tau)
                + 27.0 * cos(5.09 + 234791.13 * $tau));

        $L2 = (53050.0 * cos(0.0)
            + 16904.0 * cos(4.69072 + 26087.90314 * $tau)
            + 7397.0 * cos(1.3474 + 52175.8063 * $tau)
            + 3018.0 * cos(4.4564 + 78263.7094 * $tau)
            + 1107.0 * cos(1.2623 + 104351.6126 * $tau)
            + 378.0 * cos(4.320 + 130439.516 * $tau)
            + 123.0 * cos(1.069 + 156527.419 * $tau)
            + 39.0 * cos(4.08 + 182615.32 * $tau)
            + 15.0 * cos(4.63 + 1109.38 * $tau)
            + 12.0 * cos(0.79 + 208703.23 * $tau));

        $L3 = (188.0 * cos(0.035 + 52175.806 * $tau)
            + 142.0 * cos(3.125 + 26087.903 * $tau)
            + 97.0 * cos(3.00 + 78263.71 * $tau)
            + 44.0 * cos(6.02 + 104351.61 * $tau)
            + 35.0 * cos(0.00)
            + 18.0 * cos(2.78 + 130439.52 * $tau)
            + 7.0 * cos(5.82 + 156527.42 * $tau)
            + 3.0 * cos(2.57 + 182615.32 * $tau));
        $L4 = (114.0 * cos(3.1416)
              + 3.0 * cos(2.03 + 26087.90 * $tau)
              + 2.0 * cos(1.42 + 78263.71 * $tau)
              + 2.0 * cos(4.50 + 52175.81 * $tau)
              + 1.0 * cos(4.50 + 104351.61 * $tau)
              + 1.0 * cos(1.27 + 130439.52 * $tau));

        $L5 = (1 * cos(3.14));

        $L = (new Coordinate(rad2deg(($L0 + $L1 * $tau + $L2 * $tau ** 2 + $L3 * $tau ** 3 + $L4 * $tau ** 4 + $L5 * $tau ** 5) / 100000000)))->getCoordinate();

        // B
        $B0 = 11737529.0 * cos(1.98357499 + 26087.90314157 * $tau)
            + 2388077.0 * cos(5.0373896 + 52175.8062831 * $tau)
            + 1222840.0 * cos(3.1415927)
            + 543252.0 * cos(1.796444 + 78263.709425 * $tau)
            + 129779.0 * cos(4.832325 + 104351.612566 * $tau)
            + 31867.0 * cos(1.58088 + 130439.51571 * $tau)
            + 7963.0 * cos(4.6097 + 156527.4188 * $tau)
            + 2014.0 * cos(1.3532 + 182615.3220 * $tau)
            + 514.0 * cos(4.378 + 208703.225 * $tau)
            + 209.0 * cos(2.020 + 24978.525 * $tau)
            + 208.0 * cos(4.918 + 27197.282 * $tau)
            + 132.0 * cos(1.119 + 234791.128 * $tau)
            + 121.0 * cos(1.813 + 53285.185 * $tau)
            + 100.0 * cos(5.657 + 20426.571 * $tau);

        $B1 = (429151.0 * cos(3.501698 + 26087.903142 * $tau)
            + 146234.0 * cos(3.141593)
            + 22675.0 * cos(0.01515 + 52175.80628 * $tau)
            + 10895.0 * cos(0.48540 + 78263.70942 * $tau)
            + 6353.0 * cos(3.4294 + 104351.6126 * $tau)
            + 2496.0 * cos(0.1605 + 130439.5157 * $tau)
            + 860.0 * cos(3.185 + 156527.419 * $tau)
            + 278.0 * cos(6.210 + 182615.322 * $tau)
            + 86.0 * cos(2.95 + 208703.23 * $tau)
            + 28.0 * cos(0.29 + 27197.28 * $tau)
            + 26.0 * cos(5.98 + 234791.13 * $tau));

        $B2 = (11831.0 * cos(4.79066 + 26087.90314 * $tau)
            + 1914.0 * cos(0.0)
            + 1045.0 * cos(1.2122 + 52175.8063 * $tau)
            + 266.0 * cos(4.434 + 78263.709 * $tau)
            + 170.0 * cos(1.623 + 104351.613 * $tau)
            + 96.0 * cos(4.80 + 130439.52 * $tau)
            + 45.0 * cos(1.61 + 156527.42 * $tau)
            + 18.0 * cos(4.67 + 182615.32 * $tau)
            + 7.0 * cos(1.43 + 208703.23 * $tau));

        $B3 = (235.0 * cos(0.354 + 26087.903 * $tau)
            + 161.0 * cos(0.0)
            + 19.0 * cos(4.36 + 52175.81 * $tau)
            + 6.0 * cos(2.51 + 78263.71 * $tau)
            + 5.0 * cos(6.14 + 104351.61 * $tau)
            + 3.0 * cos(3.12 + 130439.52 * $tau)
            + 2.0 * cos(6.27 + 156527.42 * $tau));

        $B4 = (4.0 * cos(1.75 + 26087.90 * $tau)
            + 1.0 * cos(3.14));

        $B = (new Coordinate(rad2deg(($B0 + $B1 * $tau + $B2 * $tau ** 2 + $B3 * $tau ** 3 + $B4 * $tau ** 4) / 100000000), -180, 180))->getCoordinate();

        // R
        $R0 = 39528272.0 * cos(0.0)
             + 7834132.0 * cos(6.1923372 + 26087.9031416 * $tau)
             + 795526.0 * cos(2.959897 + 52175.806283 * $tau)
             + 121282.0 * cos(6.010642 + 78263.709425 * $tau)
             + 21922.0 * cos(2.77820 + 104351.61257 * $tau)
             + 4354.0 * cos(5.8289 + 130439.5157 * $tau)
             + 918.0 * cos(2.597 + 156527.419 * $tau)
             + 290.0 * cos(1.424 + 25028.521 * $tau)
             + 260.0 * cos(3.028 + 27197.282 * $tau)
             + 202.0 * cos(5.647 + 182615.322 * $tau)
             + 201.0 * cos(5.592 + 31749.235 * $tau)
             + 142.0 * cos(6.253 + 24978.525 * $tau)
             + 100.0 * cos(3.734 + 21535.950 * $tau);

        $R1 = (217348.0 * cos(4.656172 + 26087.903142 * $tau)
              + 44142.0 * cos(1.42386 + 52175.80628 * $tau)
              + 10094.0 * cos(4.47466 + 78263.70942 * $tau)
              + 2433.0 * cos(1.2423 + 104351.6126 * $tau)
              + 1624.0 * cos(0.0)
              + 604.0 * cos(4.293 + 130439.516 * $tau)
              + 153.0 * cos(1.061 + 156527.419 * $tau)
              + 39.0 * cos(4.11 + 182615.32 * $tau));

        $R2 = (3118 * cos(3.0823 + 26087.9031 * $tau)
            + 1245 * cos(6.1518 + 52175.8063 * $tau)
            + 425 * cos(2.926 + 78263.709 * $tau)
            + 136 * cos(5.980 + 104351.613 * $tau)
            + 42 * cos(2.75 + 130439.52 * $tau)
            + 22 * cos(3.14)
            + 13 * cos(5.80 + 156527.42 * $tau));

        $R3 = (33 * cos(1.68 + 26087.90 * $tau)
            + 24 * cos(4.63 + 52175.81 * $tau)
            + 12 * cos(1.39 + 78263.71 * $tau)
            + 5 * cos(4.44 + 104351.61 * $tau)
            + 2 * cos(1.21 + 130439.52 * $tau));

        $R = ($R0 + $R1 * $tau + $R2 * $tau ** 2 + $R3 * $tau ** 3) / 100000000;

        return [$L, $B, $R];
    }

    /**
     * Calculates the inferior conjunction closest to the given date.
     *
     * @param Carbon $date The date for which we want to calculate the closest inferior conjunction
     *
     * @return Carbon The date of the inferior conjunction
     *
     * Chapter 36 of Astronomical Algorithms
     */
    public function inferior_conjunction(Carbon $date): Carbon
    {
        $A  = 2451612.023;
        $B  = 115.8774771;
        $M0 = 63.5867;
        $M1 = 114.2088742;

        $Y = $date->year + $date->dayOfYear / (365 + $date->format('L'));

        $k    = ceil((365.2425 * $Y + 1721060 - $A) / ($B));
        $JDE0 = $A + $k * $B;
        $M    = deg2rad($M0 + $k * $M1);
        $T    = ($JDE0 - 2451545) / 36525;

        $diff = 0.0545 + 0.0002 * $T
            + sin($M) * (-6.2008 + 0.0074 * $T + 0.00003 * $T * $T)
            + cos($M) * (-3.2750 - 0.0197 * $T + 0.00001 * $T * $T)
            + sin(2 * $M) * (0.4737 - 0.0052 * $T - 0.00001 * $T * $T)
            + cos(2 * $M) * (0.8111 + 0.0033 * $T - 0.00002 * $T * $T)
            + sin(3 * $M) * (0.0037 + 0.0018 * $T)
            + cos(3 * $M) * (-0.1768 + 0.00001 * $T * $T)
            + sin(4 * $M) * (-0.0211 - 0.0004 * $T)
            + cos(4 * $M) * (0.0326 - 0.0003 * $T)
            + sin(5 * $M) * (0.0083 + 0.0001 * $T)
            + cos(5 * $M) * (-0.0040 + 0.0001 * $T);

        $JDE = $JDE0 + $diff;

        return Time::fromJd($JDE);
    }

    /**
     * Calculates the superior conjunction closest to the given date.
     *
     * @param Carbon $date The date for which we want to calculate the closest inferior conjunction
     *
     * @return Carbon The date of the inferior conjunction
     *
     * Chapter 36 of Astronomical Algorithms
     */
    public function superior_conjunction(Carbon $date): Carbon
    {
        $A  = 2451554.084;
        $B  = 115.8774771;
        $M0 = 6.4822;
        $M1 = 114.2088742;

        $Y = $date->year + $date->dayOfYear / (365 + $date->format('L'));

        $k    = ceil((365.2425 * $Y + 1721060 - $A) / ($B));
        $JDE0 = $A + $k * $B;
        $M    = deg2rad($M0 + $k * $M1);
        $T    = ($JDE0 - 2451545) / 36525;

        $diff = -0.0545 - 0.0002 * $T
            + sin($M) * (7.3894 - 0.0100 * $T - 0.00003 * $T * $T)
            + cos($M) * (3.2200 + 0.0197 * $T - 0.00001 * $T * $T)
            + sin(2 * $M) * (0.8383 - 0.0064 * $T - 0.00001 * $T * $T)
            + cos(2 * $M) * (0.9666 + 0.0039 * $T - 0.00003 * $T * $T)
            + sin(3 * $M) * (0.0770 - 0.0026 * $T)
            + cos(3 * $M) * (0.2758 + 0.0002 * $T - 0.00002 * $T * $T)
            + sin(4 * $M) * (-0.0128 - 0.0008 * $T)
            + cos(4 * $M) * (0.0734 - 0.0004 * $T - 0.00001 * $T * $T)
            + sin(5 * $M) * (-0.0122 - 0.0002 * $T)
            + cos(5 * $M) * (0.0173 - 0.0002 * $T);

        $JDE = $JDE0 + $diff;

        return Time::fromJd($JDE);
    }

    /**
     * Calculates the greatest eastern elongation closest to the given date. This is the best
     * evening visibility.
     *
     * @param Carbon $date The date for which we want to calculate the greatest eastern elongation
     *
     * @return Carbon The date of the greatest eastern elongation
     *
     * Chapter 36 of Astronomical Algorithms
     */
    public function greatest_eastern_elongation(Carbon $date): Carbon
    {
        $A  = 2451612.023;
        $B  = 115.8774771;
        $M0 = 63.5867;
        $M1 = 114.2088742;

        $Y = $date->year + $date->dayOfYear / (365 + $date->format('L'));

        $k    = ceil((365.2425 * $Y + 1721060 - $A) / ($B));
        $JDE0 = $A + $k * $B;
        $M    = deg2rad($M0 + $k * $M1);
        $T    = ($JDE0 - 2451545) / 36525;

        $diff = -21.6101 + 0.0002 * $T
            + sin($M) * (-1.9803 - 0.0060 * $T + 0.00001 * $T * $T)
            + cos($M) * (1.4151 - 0.0072 * $T - 0.00001 * $T * $T)
            + sin(2 * $M) * (0.5528 - 0.0005 * $T - 0.00001 * $T * $T)
            + cos(2 * $M) * (0.2905 + 0.0034 * $T + 0.00001 * $T * $T)
            + sin(3 * $M) * (-0.1121 - 0.0001 * $T + 0.00001 * $T * $T)
            + cos(3 * $M) * (-0.0098 - 0.0015 * $T)
            + sin(4 * $M) * (0.0192)
            + cos(4 * $M) * (0.0111 + 0.0004 * $T)
            + sin(5 * $M) * (-0.0061)
            + cos(5 * $M) * (-0.0032 - 0.0001 * $T);

        $JDE = $JDE0 + $diff;

        return Time::fromJd($JDE);
    }

    /**
     * Calculates the greatest western elongation closest to the given date. This is the best
     * morning visibility.
     *
     * @param Carbon $date The date for which we want to calculate the greatest western elongation
     *
     * @return Carbon The date of the greatest western elongation
     *
     * Chapter 36 of Astronomical Algorithms
     */
    public function greatest_western_elongation(Carbon $date): Carbon
    {
        $A  = 2451612.023;
        $B  = 115.8774771;
        $M0 = 63.5867;
        $M1 = 114.2088742;

        $Y = $date->year + $date->dayOfYear / (365 + $date->format('L'));

        $k    = ceil((365.2425 * $Y + 1721060 - $A) / ($B));
        $JDE0 = $A + $k * $B;
        $M    = deg2rad($M0 + $k * $M1);
        $T    = ($JDE0 - 2451545) / 36525;

        $diff = 21.6249 - 0.0002 * $T
            + sin($M) * (0.1306 + 0.0065 * $T)
            + cos($M) * (-2.7661 - 0.0011 * $T + 0.00001 * $T * $T)
            + sin(2 * $M) * (0.2438 - 0.0024 * $T - 0.00001 * $T * $T)
            + cos(2 * $M) * (0.5767 + 0.0023 * $T)
            + sin(3 * $M) * (0.1041)
            + cos(3 * $M) * (-0.0184 + 0.0007 * $T)
            + sin(4 * $M) * (-0.0051 - 0.0001 * $T)
            + cos(4 * $M) * (0.0048 + 0.0001 * $T)
            + sin(5 * $M) * (0.0026)
            + cos(5 * $M) * (0.0037);

        $JDE = $JDE0 + $diff;

        return Time::fromJd($JDE);
    }

    /**
     * Returns the date of perihelion closest to the given date.
     *
     * @param Carbon $date The date for which we want to calculate the closest perihelion
     *
     * @return Carbon The date of the perihelion
     *
     * Chapter 38 of Astronomical Algorithms
     */
    public function perihelionDate(Carbon $date): Carbon
    {
        $Y = $date->year + $date->dayOfYear / (365 + $date->format('L'));

        // $k is integer
        $k = round(4.15201 * ($Y - 2000.12));

        $JDE = 2451590.257 + 87.96934963 * $k - 0.0000000000 * $k * $k;

        return Time::fromJd($JDE);
    }

    /**
     * Returns the date of aphelion closest to the given date.
     *
     * @param Carbon $date The date for which we want to calculate the closest aphelion
     *
     * @return Carbon The date of the aphelion
     *
     * Chapter 38 of Astronomical Algorithms
     */
    public function aphelionDate(Carbon $date): Carbon
    {
        $Y = $date->year + $date->dayOfYear / (365 + $date->format('L'));

        // $k is integer increased by 0.5
        $k   = round(4.15201 * ($Y - 2000.12)) + 0.5;
        $JDE = 2451590.257 + 87.96934963 * $k - 0.0000000000 * $k * $k;

        return Time::fromJd($JDE);
    }

    /**
     * Calculates the magnitude at the given date.
     *
     * @param Carbon $date The date for which we want to calculate the magnitude
     *
     * @return float The magnitude
     *
     * Chapter 41 of Astronomical Algorithms
     */
    public function magnitude(Carbon $date): float
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);
        $R            = $helio_coords[2];

        $earth              = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);
        $R0                 = $helio_coords_earth[2];

        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);

        $i = rad2deg(acos(($R - $R0 * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0] - $helio_coords_earth[0]))) / $delta));

        return round(1.16 + 5 * log10($R * $delta) + 0.02838 * ($i - 50) + 0.0001023 * ($i - 50) ** 2, 1);
    }

    /**
     * Calculate the diameter of Mercury.  You can get the diamter
     * by using the getDiameter method.
     *
     * @param Carbon $date The date
     *
     * @return None
     *
     * Chapter 55 of Astronomical Algorithms
     */
    public function calculateDiameter(Carbon $date)
    {
        $helio_coords = $this->calculateHeliocentricCoordinates($date);

        $earth              = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);
        $x                  = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
                    $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
                    $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
                    $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);

        $this->setDiameter(round(2 * 3.36 / $delta, 1));
    }
}
