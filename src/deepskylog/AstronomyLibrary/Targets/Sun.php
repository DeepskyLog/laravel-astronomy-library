<?php

/**
 * The target class describing the sun.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\RectangularCoordinates;
use deepskylog\AstronomyLibrary\Time;

/**
 * The target class describing the sun.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */
class Sun extends Target
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(-0.8333);
    }

    /**
     * Calculates the equatorial coordinates of the sun with a low accuracy (0.01 degree).
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  float  $obliquity  The obliquity of the ecliptic for the given date
     *
     * See chapter 25 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // Accept variadic args for compatibility with Target::calculateEquatorialCoordinates
        // Expected: [$obliquity]
        $obliquity = $args[0] ?? null;

        if ($obliquity === null) {
            // If obliquity not provided, try to derive a reasonable default using nutation
            $nutation = Time::nutation(Time::getJd($date));
            $obliquity = $nutation[3];
        }

        $obliquity = floatval($obliquity);

        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $obliquity)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay(), $obliquity)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2), $obliquity)
        );
    }

    /**
     * Calculates the equatorial coordinates of the sun with a low accuracy (0.01 degree).
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  float  $obliquity  The obliquity of the ecliptic for the given date
     * @return EquatorialCoordinates The equatorial coordinates for the given date.
     *
     * See chapter 25 of Astronomical Algorithms
     */
    private function _calculateEquatorialCoordinates(Carbon $date, float $obliquity): EquatorialCoordinates
    {
        // T = julian centuries since epoch J2000.0
        $julian_centuries = (Time::getJd($date) - 2451545.0) / 36525.0;

        // Lo = Geometric mean longitude of the sun, referred to the main equinox of date
        $Lo = (new Coordinate(280.46646 + 36000.76983 * $julian_centuries + 0.0003032 * $julian_centuries ** 2))->getCoordinate();

        // M = Mean anomaly of the sun
        $M = (new Coordinate(357.52911 + 35999.05029 * $julian_centuries - 0.0001537 * $julian_centuries ** 2))->getCoordinate();

        // e = Eccentricity of the earth's orbit
        $e = 0.016708634 - 0.000042037 * $julian_centuries - 0.0000001267 * $julian_centuries ** 2;

        // C = Sun's equation of the center C
        $C = (1.914602 - 0.004817 * $julian_centuries - 0.000014 * $julian_centuries ** 2) * sin(deg2rad($M))
            + (0.019993 - 0.000101 * $julian_centuries) * sin(deg2rad(2 * $M))
            + 0.000289 * sin(deg2rad(3 * $M));

        // Odot = Sun's true longitude
        $Odot = $Lo + $C;

        // nu = Sun's true anomaly
        $nu = $M + $C;

        // R = radius vector
        $R = (1.000001018 * (1 - $e ** 2)) / (1 + $e * cos(deg2rad($nu)));

        $omega = 125.04 - 1934.136 * $julian_centuries;
        $lambda = $Odot - 0.00569 - 0.00478 * sin(deg2rad($omega));

        $ra = rad2deg(
            atan2(
                cos(deg2rad($obliquity)) * sin(deg2rad($Odot)),
                cos(deg2rad($Odot))
            )
        ) / 15.0;

        $decl = rad2deg(
            asin(
                sin(deg2rad($obliquity)) * sin(deg2rad($Odot))
            )
        );

        return new EquatorialCoordinates($ra, $decl);
    }

    /**
     * Calculates the equatorial coordinates of the sun with a high accuracy.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  array  $nutation  The nutation
     *
     * See chapter 25 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinatesHighAccuracy(Carbon $date, array $nutation): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinatesHighAccuracy($date, $nutation)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinatesHighAccuracy($date->addDay(), $nutation)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinatesHighAccuracy($date->subDays(2), $nutation)
        );
    }

    /**
     * Calculates the equatorial coordinates of the sun with a high accuracy.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  array  $nutation  The nutation
     * @return EquatorialCoordinates The coordinates for the given date
     *
     * See chapter 25 of Astronomical Algorithms
     */
    private function _calculateEquatorialCoordinatesHighAccuracy(Carbon $date, array $nutation): EquatorialCoordinates
    {
        [$Odot, $beta, $R] = $this->calculateOdotBetaR($date);

        $lambda = $Odot + ($nutation[0] - 20.4898 / $R) / 3600.0;
        $ecl = new EclipticalCoordinates($lambda, $beta);

        return $ecl->convertToEquatorial($nutation[3]);
    }

    /**
     * Calculates the equatorial coordinates of the sun with a high accuracy.
     *
     * @param  Carbon  $date  The date for which to calculate the coordinates
     * @param  array  $nutation  The nutation
     * @return EquatorialCoordinates The coordinates for the given date
     *
     * See chapter 25 of Astronomical Algorithms
     */
    public function calculateOdotBetaR(Carbon $date): array
    {
        // tau = julian millenia since epoch J2000.0
        $tau = (Time::getJd($date) - 2451545.0) / 365250.0;

        $L = $this->_calculateL($tau);

        $B0 = 280 * cos(3.199 + 84334.662 * $tau)
                + 102 * cos(5.422 + 5507.553 * $tau)
                + 80 * cos(3.88 + 5223.69 * $tau)
                + 44 * cos(3.70 + 2352.87 * $tau)
                + 32 * cos(4.00 + 1577.34 * $tau);

        $B1 = (9 * cos(3.90 + 5507.55 * $tau)
                 + 6 * cos(1.73 + 5223.69 * $tau)) * $tau;

        $B = (new Coordinate(rad2deg(($B0 + $B1) / pow(10, 8)), -90, 90))->getCoordinate();

        $R = $this->_calculateR($tau);

        $Odot = $L + 180;
        $beta = -$B * 3600.0;

        $lambda_accent = $Odot - 1.397 * 10 * $tau - 0.00031 * (10 * $tau) ** 2;
        $delta_Odot = -0.09033;
        $delta_beta = 0.03916 * (cos(deg2rad($lambda_accent)) - sin(deg2rad($lambda_accent)));

        $Odot = $Odot + $delta_Odot / 3600.0;
        $beta = ($beta + $delta_beta) / 3600.0;

        return [$Odot, $beta, $R];
    }

    /**
     * Calculates L for the calculation of the coordinates of the sun.
     *
    * @param  float  $tau  julian millenia since epoch J2000.0
     * @return float L
     *
     * See chapter 25 of Astronomical Algorithms
     */
    private function _calculateL($tau): float
    {
        $L0 = 175347046.0 * cos(0.0)
        + 3341656.0 * cos(4.6692568 + 6283.0758500 * $tau)
        + 34894.0 * cos(4.62610 + 12566.15170 * $tau)
        + 3497 * cos(2.7441 + 5753.3849 * $tau)
        + 3418 * cos(2.8289 + 3.5231 * $tau)
        + 3136 * cos(3.6277 + 77713.7715 * $tau)
        + 2676 * cos(4.4181 + 7860.4194 * $tau)
        + 2343 * cos(6.1352 + 3930.2097 * $tau)
        + 1324 * cos(0.7425 + 11506.7698 * $tau)
        + 1273 * cos(2.0371 + 529.6910 * $tau)
        + 1199 * cos(1.1096 + 1577.3435 * $tau)
        + 990 * cos(5.233 + 5884.927 * $tau)
        + 902 * cos(2.045 + 26.298 * $tau)
        + 857 * cos(3.508 + 398.149 * $tau)
        + 780 * cos(1.179 + 5223.694 * $tau)
        + 753 * cos(2.533 + 5507.553 * $tau)
        + 505 * cos(4.583 + 18849.228 * $tau)
        + 492 * cos(4.205 + 775.523 * $tau)
        + 357 * cos(2.920 + 0.067 * $tau)
        + 317 * cos(5.849 + 11790.629 * $tau)
        + 284 * cos(1.899 + 796.298 * $tau)
        + 271 * cos(0.315 + 10977.079 * $tau)
        + 243 * cos(0.345 + 5486.778 * $tau)
        + 206 * cos(4.806 + 2544.314 * $tau)
        + 205 * cos(1.869 + 5573.143 * $tau)
        + 202 * cos(2.458 + 6069.777 * $tau)
        + 156 * cos(0.833 + 213.299 * $tau)
        + 132 * cos(3.411 + 2942.463 * $tau)
        + 126 * cos(1.083 + 20.775 * $tau)
        + 115 * cos(0.645 + 0.980 * $tau)
        + 103 * cos(0.636 + 4694.003 * $tau)
        + 102 * cos(0.976 + 15720.839 * $tau)
        + 102 * cos(4.267 + 7.114 * $tau)
        + 99 * cos(6.21 + 2146.17 * $tau)
        + 98 * cos(0.68 + 155.42 * $tau)
        + 86 * cos(5.98 + 161000.69 * $tau)
        + 85 * cos(1.30 + 6275.96 * $tau)
        + 85 * cos(3.67 + 71430.70 * $tau)
        + 80 * cos(1.81 + 17260.15 * $tau)
        + 79 * cos(3.04 + 12036.46 * $tau)
        + 75 * cos(1.76 + 5088.63 * $tau)
        + 74 * cos(3.50 + 3154.69 * $tau)
        + 74 * cos(4.68 + 801.82 * $tau)
        + 70 * cos(0.83 + 9437.76 * $tau)
        + 62 * cos(3.98 + 8827.39 * $tau)
        + 61 * cos(1.82 + 7084.90 * $tau)
        + 57 * cos(2.78 + 6286.60 * $tau)
        + 56 * cos(4.39 + 14143.50 * $tau)
        + 56 * cos(3.47 + 6279.55 * $tau)
        + 52 * cos(0.19 + 12139.55 * $tau)
        + 52 * cos(1.33 + 1748.02 * $tau)
        + 51 * cos(0.28 + 5856.48 * $tau)
        + 49 * cos(0.49 + 1194.45 * $tau)
        + 41 * cos(5.37 + 8429.24 * $tau)
        + 41 * cos(2.40 + 19651.05 * $tau)
        + 39 * cos(6.17 + 10447.39 * $tau)
        + 37 * cos(6.04 + 10213.29 * $tau)
        + 37 * cos(2.57 + 1059.38 * $tau)
        + 36 * cos(1.71 + 2352.87 * $tau)
        + 36 * cos(1.78 + 6812.77 * $tau)
        + 33 * cos(0.59 + 17789.85 * $tau)
        + 30 * cos(0.44 + 83996.85 * $tau)
        + 30 * cos(2.74 + 1349.87 * $tau)
        + 25 * cos(3.16 + 4690.48 * $tau);

        $L1 = (628331966747.0 * cos(0.0)
        + 206059.0 * cos(2.678235 + 6283.075850 * $tau)
        + 4303 * cos(2.6351 + 12566.1517 * $tau)
        + 425 * cos(1.590 + 3.523 * $tau)
        + 119 * cos(5.796 + 26.298 * $tau)
        + 109 * cos(2.966 + 1577.344 * $tau)
        + 93 * cos(2.59 + 18849.23 * $tau)
        + 72 * cos(1.14 + 529.69 * $tau)
        + 68 * cos(1.87 + 398.15 * $tau)
        + 67 * cos(4.41 + 5507.55 * $tau)
        + 59 * cos(2.89 + 5223.69 * $tau)
        + 56 * cos(2.17 + 155.42 * $tau)
        + 45 * cos(0.40 + 796.30 * $tau)
        + 36 * cos(0.47 + 775.52 * $tau)
        + 29 * cos(2.65 + 7.11 * $tau)
        + 21 * cos(5.34 + 0.98 * $tau)
        + 19 * cos(1.85 + 5486.78 * $tau)
        + 19 * cos(4.97 + 213.30 * $tau)
        + 17 * cos(2.99 + 6275.96 * $tau)
        + 16 * cos(0.03 + 2544.31 * $tau)
        + 16 * cos(1.43 + 2146.17 * $tau)
        + 15 * cos(1.21 + 10977.08 * $tau)
        + 12 * cos(2.83 + 1748.02 * $tau)
        + 12 * cos(3.26 + 5088.63 * $tau)
        + 12 * cos(5.27 + 1194.45 * $tau)
        + 12 * cos(2.08 + 4694.00 * $tau)
        + 11 * cos(0.77 + 553.57 * $tau)
        + 10 * cos(1.30 + 6286.60 * $tau)
        + 10 * cos(4.24 + 1349.87 * $tau)
        + 9 * cos(2.70 + 242.73 * $tau)
        + 9 * cos(5.64 + 951.72 * $tau)
        + 8 * cos(5.30 + 2352.87 * $tau)
        + 6 * cos(2.65 + 9437.76 * $tau)
        + 6 * cos(4.67 + 4690.48 * $tau)) * $tau;

        $L2 = (52919.0 * cos(0.0)
        + 8720 * cos(1.0721 + 6283.0758 * $tau)
        + 309 * cos(0.867 + 12566.152 * $tau)
        + 27 * cos(0.05 + 3.52 * $tau)
        + 16 * cos(5.19 + 26.30 * $tau)
        + 16 * cos(3.68 + 155.42 * $tau)
        + 10 * cos(0.76 + 18849.23 * $tau)
        + 9 * cos(2.06 + 77713.77 * $tau)
        + 7 * cos(0.83 + 775.52 * $tau)
        + 5 * cos(4.66 + 1577.34 * $tau)
        + 4 * cos(1.03 + 7.11 * $tau)
        + 4 * cos(3.44 + 5573.14 * $tau)
        + 3 * cos(5.14 + 796.30 * $tau)
        + 3 * cos(6.05 + 5507.55 * $tau)
        + 3 * cos(1.19 + 242.73 * $tau)
        + 3 * cos(6.12 + 529.69 * $tau)
        + 3 * cos(0.31 + 398.15 * $tau)
        + 3 * cos(2.28 + 553.57 * $tau)
        + 2 * cos(4.38 + 5223.69 * $tau)
        + 2 * cos(3.75 + 0.98 * $tau)) * pow($tau, 2);

        $L3 = (289 * cos(5.844 + 6283.076 * $tau)
        + 35 * cos(0.0)
        + 17 * cos(5.49 + 12566.15 * $tau)
        + 3 * cos(5.20 + 155.42 * $tau)
        + 1 * cos(4.72 + 3.52 * $tau)
        + 1 * cos(5.30 + 18849.23 * $tau)
        + 1 * cos(5.97 + 242.73 * $tau)) * pow($tau, 3);

        $L4 = (114 * cos(3.142)
        + 8 * cos(4.13 + 6283.08 * $tau)
        + 1 * cos(3.84 + 12566.15 * $tau)) * pow($tau, 4);

        $L5 = (1 * cos(3.14)) * pow($tau, 5);

        $L = (new Coordinate(rad2deg(($L0 + $L1 + $L2 + $L3 + $L4 + $L5) / pow(10, 8))))->getCoordinate();

        return $L;
    }

    /**
     * Calculates R for the calculation of the coordinates of the sun.
     *
    * @param  float  $tau  julian millenia since epoch J2000.0
     * @return float R
     *
     * See chapter 25 of Astronomical Algorithms
     */
    private function _calculateR($tau): float
    {
        $R0 = 100013989.0 * cos(0.0)
                + 1670700.0 * cos(3.0984635 + 6283.0758500 * $tau)
                + 13956 * cos(3.05525 + 12566.15170 * $tau)
                + 3084 * cos(5.1985 + 77713.7715 * $tau)
                + 1628 * cos(1.1739 + 5753.3849 * $tau)
                + 1576 * cos(2.8469 + 7860.4194 * $tau)
                + 925 * cos(5.453 + 11506.770 * $tau)
                + 542 * cos(4.564 + 3930.210 * $tau)
                + 472 * cos(3.661 + 5884.927 * $tau)
                + 346 * cos(0.964 + 5507.553 * $tau)
                + 329 * cos(5.900 + 5223.694 * $tau)
                + 307 * cos(0.299 + 5573.143 * $tau)
                + 243 * cos(4.273 + 11790.629 * $tau)
                + 212 * cos(5.847 + 1577.344 * $tau)
                + 186 * cos(5.022 + 10977.079 * $tau)
                + 175 * cos(3.012 + 18849.228 * $tau)
                + 110 * cos(5.055 + 5486.778 * $tau)
                + 98 * cos(0.89 + 6069.78 * $tau)
                + 86 * cos(5.69 + 15720.84 * $tau)
                + 86 * cos(1.27 + 161000.69 * $tau)
                + 65 * cos(0.27 + 17260.15 * $tau)
                + 63 * cos(0.92 + 529.69 * $tau)
                + 57 * cos(2.01 + 83996.85 * $tau)
                + 56 * cos(5.24 + 71430.70 * $tau)
                + 49 * cos(3.25 + 2544.31 * $tau)
                + 47 * cos(2.58 + 775.52 * $tau)
                + 45 * cos(5.54 + 9437.76 * $tau)
                + 43 * cos(6.01 + 6275.96 * $tau)
                + 39 * cos(5.36 + 4694.00 * $tau)
                + 38 * cos(2.39 + 8827.39 * $tau)
                + 37 * cos(0.83 + 19651.05 * $tau)
                + 37 * cos(4.90 + 12139.55 * $tau)
                + 36 * cos(1.67 + 12036.46 * $tau)
                + 35 * cos(1.84 + 2942.46 * $tau)
                + 33 * cos(0.24 + 7084.90 * $tau)
                + 32 * cos(0.18 + 5088.63 * $tau)
                + 32 * cos(1.78 + 398.15 * $tau)
                + 28 * cos(1.21 + 6286.60 * $tau)
                + 28 * cos(1.90 + 6279.55 * $tau)
                + 26 * cos(4.59 + 10447.39 * $tau);

        $R1 = (103019.0 * cos(1.107490 + 6283.075850 * $tau)
            + 1721 * cos(1.0644 + 12566.1517 * $tau)
            + 702 * cos(3.142)
            + 32 * cos(1.02 + 18849.23 * $tau)
            + 31 * cos(2.84 + 5507.55 * $tau)
            + 25 * cos(1.32 + 5223.69 * $tau)
            + 18 * cos(1.42 + 1577.34 * $tau)
            + 10 * cos(5.91 + 10977.08 * $tau)
            + 9 * cos(1.42 + 6275.96 * $tau)
            + 9 * cos(0.27 + 5486.78 * $tau)) * $tau;

        $R2 = (4359 * cos(5.7846 + 6283.0758 * $tau)
            + 124 * cos(5.579 + 12566.152 * $tau)
            + 12 * cos(3.14)
            + 9 * cos(3.63 + 77713.77 * $tau)
            + 6 * cos(1.87 + 5573.14 * $tau)
            + 3 * cos(5.47 + 18849.23 * $tau)) * pow($tau, 2);

        $R3 = (145 * cos(4.273 + 6283.076 * $tau)
            + 7 * cos(3.92 + 12566.15 * $tau)) * pow($tau, 3);

        $R4 = (4 * cos(2.56 + 6283.08 * $tau)) * pow($tau, 4);

        $R = ($R0 + $R1 + $R2 + $R3 + $R4) / pow(10, 8);

        return $R;
    }

    /**
     * Calculates the geometric coordinates of the sun for the equinox of the date.
     *
     * @param  Carbon  $date  The date
     * @return RectangularCoordinates The rectangular Coordinates
     *
     * See chapter 26 of Astronomical Algorithms
     */
    public function calculateGeometricCoordinates(Carbon $date): RectangularCoordinates
    {
        [$Odot, $beta, $R] = $this->calculateOdotBetaR($date);

        $nutation = Time::nutation(Time::getJd($date));

        $X = $R * cos(deg2rad($beta)) * cos(deg2rad($Odot));
        $Y = $R * (cos(deg2rad($beta)) * sin(deg2rad($Odot)) * cos(deg2rad($nutation[2]))
            - sin(deg2rad($beta)) * sin(deg2rad($nutation[2])));
        $Z = $R * (cos(deg2rad($beta)) * sin(deg2rad($Odot)) * sin(deg2rad($nutation[2]))
            + sin(deg2rad($beta)) * cos(deg2rad($nutation[2])));

        return new RectangularCoordinates($X, $Y, $Z);
    }

    /**
     * Calculates the geometric coordinates of the sun for the J2000 equinox.
     *
     * @param  Carbon  $date  The date
     * @return RectangularCoordinates The rectangular Coordinates
     *
     * See chapter 26 of Astronomical Algorithms
     */
    public function calculateGeometricCoordinatesJ2000(Carbon $date): RectangularCoordinates
    {
        // tau = julian millenia since epoch J2000.0
        $tau = (Time::getJd($date) - 2451545.0) / 365250.0;

        $L0 = 175347046.0 * cos(0.0)
            + 3341656.0 * cos(4.6692568 + 6283.0758500 * $tau)
            + 34894.0 * cos(4.62610 + 12566.15170 * $tau)
            + 3497 * cos(2.7441 + 5753.3849 * $tau)
            + 3418 * cos(2.8289 + 3.5231 * $tau)
            + 3136 * cos(3.6277 + 77713.7715 * $tau)
            + 2676 * cos(4.4181 + 7860.4194 * $tau)
            + 2343 * cos(6.1352 + 3930.2097 * $tau)
            + 1324 * cos(0.7425 + 11506.7698 * $tau)
            + 1273 * cos(2.0371 + 529.6910 * $tau)
            + 1199 * cos(1.1096 + 1577.3435 * $tau)
            + 990 * cos(5.233 + 5884.927 * $tau)
            + 902 * cos(2.045 + 26.298 * $tau)
            + 857 * cos(3.508 + 398.149 * $tau)
            + 780 * cos(1.179 + 5223.694 * $tau)
            + 753 * cos(2.533 + 5507.553 * $tau)
            + 505 * cos(4.583 + 18849.228 * $tau)
            + 492 * cos(4.205 + 775.523 * $tau)
            + 357 * cos(2.920 + 0.067 * $tau)
            + 317 * cos(5.849 + 11790.629 * $tau)
            + 284 * cos(1.899 + 796.298 * $tau)
            + 271 * cos(0.315 + 10977.079 * $tau)
            + 243 * cos(0.345 + 5486.778 * $tau)
            + 206 * cos(4.806 + 2544.314 * $tau)
            + 205 * cos(1.869 + 5573.143 * $tau)
            + 202 * cos(2.458 + 6069.777 * $tau)
            + 156 * cos(0.833 + 213.299 * $tau)
            + 132 * cos(3.411 + 2942.463 * $tau)
            + 126 * cos(1.083 + 20.775 * $tau)
            + 115 * cos(0.645 + 0.980 * $tau)
            + 103 * cos(0.636 + 4694.003 * $tau)
            + 102 * cos(0.976 + 15720.839 * $tau)
            + 102 * cos(4.267 + 7.114 * $tau)
            + 99 * cos(6.21 + 2146.17 * $tau)
            + 98 * cos(0.68 + 155.42 * $tau)
            + 86 * cos(5.98 + 161000.69 * $tau)
            + 85 * cos(1.30 + 6275.96 * $tau)
            + 85 * cos(3.67 + 71430.70 * $tau)
            + 80 * cos(1.81 + 17260.15 * $tau)
            + 79 * cos(3.04 + 12036.46 * $tau)
            + 75 * cos(1.76 + 5088.63 * $tau)
            + 74 * cos(3.50 + 3154.69 * $tau)
            + 74 * cos(4.68 + 801.82 * $tau)
            + 70 * cos(0.83 + 9437.76 * $tau)
            + 62 * cos(3.98 + 8827.39 * $tau)
            + 61 * cos(1.82 + 7084.90 * $tau)
            + 57 * cos(2.78 + 6286.60 * $tau)
            + 56 * cos(4.39 + 14143.50 * $tau)
            + 56 * cos(3.47 + 6279.55 * $tau)
            + 52 * cos(0.19 + 12139.55 * $tau)
            + 52 * cos(1.33 + 1748.02 * $tau)
            + 51 * cos(0.28 + 5856.48 * $tau)
            + 49 * cos(0.49 + 1194.45 * $tau)
            + 41 * cos(5.37 + 8429.24 * $tau)
            + 41 * cos(2.40 + 19651.05 * $tau)
            + 39 * cos(6.17 + 10447.39 * $tau)
            + 37 * cos(6.04 + 10213.29 * $tau)
            + 37 * cos(2.57 + 1059.38 * $tau)
            + 36 * cos(1.71 + 2352.87 * $tau)
            + 36 * cos(1.78 + 6812.77 * $tau)
            + 33 * cos(0.59 + 17789.85 * $tau)
            + 30 * cos(0.44 + 83996.85 * $tau)
            + 30 * cos(2.74 + 1349.87 * $tau)
            + 25 * cos(3.16 + 4690.48 * $tau);

        $L1 = (628307584999.0 * cos(0.0)
            + 206059.0 * cos(2.678235 + 6283.075850 * $tau)
            + 4303 * cos(2.6351 + 12566.1517 * $tau)
            + 425 * cos(1.590 + 3.523 * $tau)
            + 119 * cos(5.796 + 26.298 * $tau)
            + 109 * cos(2.966 + 1577.344 * $tau)
            + 93 * cos(2.59 + 18849.23 * $tau)
            + 72 * cos(1.14 + 529.69 * $tau)
            + 68 * cos(1.87 + 398.15 * $tau)
            + 67 * cos(4.41 + 5507.55 * $tau)
            + 59 * cos(2.89 + 5223.69 * $tau)
            + 56 * cos(2.17 + 155.42 * $tau)
            + 45 * cos(0.40 + 796.30 * $tau)
            + 36 * cos(0.47 + 775.52 * $tau)
            + 29 * cos(2.65 + 7.11 * $tau)
            + 21 * cos(5.34 + 0.98 * $tau)
            + 19 * cos(1.85 + 5486.78 * $tau)
            + 19 * cos(4.97 + 213.30 * $tau)
            + 17 * cos(2.99 + 6275.96 * $tau)
            + 16 * cos(0.03 + 2544.31 * $tau)
            + 16 * cos(1.43 + 2146.17 * $tau)
            + 15 * cos(1.21 + 10977.08 * $tau)
            + 12 * cos(2.83 + 1748.02 * $tau)
            + 12 * cos(3.26 + 5088.63 * $tau)
            + 12 * cos(5.27 + 1194.45 * $tau)
            + 12 * cos(2.08 + 4694.00 * $tau)
            + 11 * cos(0.77 + 553.57 * $tau)
            + 10 * cos(1.30 + 6286.60 * $tau)
            + 10 * cos(4.24 + 1349.87 * $tau)
            + 9 * cos(2.70 + 242.73 * $tau)
            + 9 * cos(5.64 + 951.72 * $tau)
            + 8 * cos(5.30 + 2352.87 * $tau)
            + 6 * cos(2.65 + 9437.76 * $tau)
            + 6 * cos(4.67 + 4690.48 * $tau)) * $tau;

        $L2 = (8722.0 * cos(1.0725 + 6283.0758 * $tau)
            + 991 * cos(3.1416)
            + 295 * cos(0.437 + 12566.152 * $tau)
            + 27 * cos(0.05 + 3.52 * $tau)
            + 16 * cos(5.19 + 26.30 * $tau)
            + 16 * cos(3.69 + 155.42 * $tau)
            + 9 * cos(0.30 + 18849.23 * $tau)
            + 9 * cos(2.06 + 77713.77 * $tau)
            + 7 * cos(0.83 + 775.52 * $tau)
            + 5 * cos(4.66 + 1577.34 * $tau)
            + 4 * cos(1.03 + 7.11 * $tau)
            + 4 * cos(3.44 + 5573.14 * $tau)
            + 3 * cos(5.14 + 796.30 * $tau)
            + 3 * cos(6.05 + 5507.55 * $tau)
            + 3 * cos(1.19 + 242.73 * $tau)
            + 3 * cos(6.12 + 529.69 * $tau)
            + 3 * cos(0.30 + 398.15 * $tau)
            + 3 * cos(2.28 + 553.57 * $tau)
            + 2 * cos(4.38 + 5223.69 * $tau)
            + 2 * cos(3.75 + 0.98 * $tau)) * pow($tau, 2);

        $L3 = (289 * cos(5.842 + 6283.076 * $tau)
            + 21 * cos(6.05 + 12566.15 * $tau)
            + 3 * cos(5.20 + 155.42 * $tau)
            + 3 * cos(3.14)
            + 1 * cos(4.72 + 3.52 * $tau)
            + 1 * cos(5.97 + 242.73 * $tau)
            + 1 * cos(5.54 + 18849.23 * $tau)) * pow($tau, 3);

        $L4 = (8 * cos(4.14 + 6283.08 * $tau)
            + 1 * cos(3.28 + 12566.15 * $tau)) * pow($tau, 4);

        $L = (new Coordinate(rad2deg(($L0 + $L1 + $L2 + $L3 + $L4) / pow(10, 8))))->getCoordinate();

        $B0 = 280 * cos(3.199 + 84334.662 * $tau)
                + 102 * cos(5.422 + 5507.553 * $tau)
                + 80 * cos(3.88 + 5223.69 * $tau)
                + 44 * cos(3.70 + 2352.87 * $tau)
                + 32 * cos(4.00 + 1577.34 * $tau);

        $B1 = (227778 * cos(3.413766 + 6283.075850 * $tau)
                + 3806 * cos(3.3706 + 12566.1517 * $tau)
                + 3620 * cos(0.0)
                + 72 * cos(3.33 + 18849 * $tau)
                + 8 * cos(3.89 + 5507.55 * $tau)
                + 8 * cos(1.79 + 5223.69 * $tau)
                + 6 * cos(5.20 + 2352.87 * $tau)) * $tau;

        $B2 = (9721 * cos(5.1519 + 6283.07585 * $tau)
                + 233 * cos(3.1416)
                + 134 * cos(0.644 + 12566.152 * $tau)
                + 7 * cos(1.07 + 18849.23 * $tau)) * pow($tau, 2);

        $B3 = (276 * cos(0.595 + 6283.076 * $tau)
                + 17 * cos(3.14)
                + 4 * cos(0.12 + 12566.15 * $tau)) * pow($tau, 3);

        $B4 = (6 * cos(2.27 + 6283.08 * $tau)
                 + 1 * cos(0.0)) * pow($tau, 4);

        $B = (new Coordinate(rad2deg(($B0 + $B1 + $B2 + $B3 + $B4) / pow(10, 8)), -90, 90))->getCoordinate();

        $R0 = 100013989.0 * cos(0.0)
                    + 1670700.0 * cos(3.0984635 + 6283.0758500 * $tau)
                    + 13956 * cos(3.05525 + 12566.15170 * $tau)
                    + 3084 * cos(5.1985 + 77713.7715 * $tau)
                    + 1628 * cos(1.1739 + 5753.3849 * $tau)
                    + 1576 * cos(2.8469 + 7860.4194 * $tau)
                    + 925 * cos(5.453 + 11506.770 * $tau)
                    + 542 * cos(4.564 + 3930.210 * $tau)
                    + 472 * cos(3.661 + 5884.927 * $tau)
                    + 346 * cos(0.964 + 5507.553 * $tau)
                    + 329 * cos(5.900 + 5223.694 * $tau)
                    + 307 * cos(0.299 + 5573.143 * $tau)
                    + 243 * cos(4.273 + 11790.629 * $tau)
                    + 212 * cos(5.847 + 1577.344 * $tau)
                    + 186 * cos(5.022 + 10977.079 * $tau)
                    + 175 * cos(3.012 + 18849.228 * $tau)
                    + 110 * cos(5.055 + 5486.778 * $tau)
                    + 98 * cos(0.89 + 6069.78 * $tau)
                    + 86 * cos(5.69 + 15720.84 * $tau)
                    + 86 * cos(1.27 + 161000.69 * $tau)
                    + 65 * cos(0.27 + 17260.15 * $tau)
                    + 63 * cos(0.92 + 529.69 * $tau)
                    + 57 * cos(2.01 + 83996.85 * $tau)
                    + 56 * cos(5.24 + 71430.70 * $tau)
                    + 49 * cos(3.25 + 2544.31 * $tau)
                    + 47 * cos(2.58 + 775.52 * $tau)
                    + 45 * cos(5.54 + 9437.76 * $tau)
                    + 43 * cos(6.01 + 6275.96 * $tau)
                    + 39 * cos(5.36 + 4694.00 * $tau)
                    + 38 * cos(2.39 + 8827.39 * $tau)
                    + 37 * cos(0.83 + 19651.05 * $tau)
                    + 37 * cos(4.90 + 12139.55 * $tau)
                    + 36 * cos(1.67 + 12036.46 * $tau)
                    + 35 * cos(1.84 + 2942.46 * $tau)
                    + 33 * cos(0.24 + 7084.90 * $tau)
                    + 32 * cos(0.18 + 5088.63 * $tau)
                    + 32 * cos(1.78 + 398.15 * $tau)
                    + 28 * cos(1.21 + 6286.60 * $tau)
                    + 28 * cos(1.90 + 6279.55 * $tau)
                    + 26 * cos(4.59 + 10447.39 * $tau);

        $R1 = (103019.0 * cos(1.107490 + 6283.075850 * $tau)
                + 1721 * cos(1.0644 + 12566.1517 * $tau)
                + 702 * cos(3.142)
                + 32 * cos(1.02 + 18849.23 * $tau)
                + 31 * cos(2.84 + 5507.55 * $tau)
                + 25 * cos(1.32 + 5223.69 * $tau)
                + 18 * cos(1.42 + 1577.34 * $tau)
                + 10 * cos(5.91 + 10977.08 * $tau)
                + 9 * cos(1.42 + 6275.96 * $tau)
                + 9 * cos(0.27 + 5486.78 * $tau)) * $tau;

        $R2 = (4359 * cos(5.7846 + 6283.0758 * $tau)
                + 124 * cos(5.579 + 12566.152 * $tau)
                + 12 * cos(3.14)
                + 9 * cos(3.63 + 77713.77 * $tau)
                + 6 * cos(1.87 + 5573.14 * $tau)
                + 3 * cos(5.47 + 18849.23 * $tau)) * pow($tau, 2);

        $R3 = (145 * cos(4.273 + 6283.076 * $tau)
                + 7 * cos(3.92 + 12566.15 * $tau)) * pow($tau, 3);

        $R4 = (4 * cos(2.56 + 6283.08 * $tau)) * pow($tau, 4);

        $R = ($R0 + $R1 + $R2 + $R3 + $R4) / pow(10, 8);

        $Odot = $L + 180;
        $beta = -$B;

        $X = $R * cos(deg2rad($beta)) * cos(deg2rad($Odot));
        $Y = $R * cos(deg2rad($beta)) * sin(deg2rad($Odot));
        $Z = $R * sin(deg2rad($beta));

        $X0 = $X + 0.000000440360 * $Y - 0.000000190919 * $Z;
        $Y0 = -0.000000479966 * $X + 0.917482137087 * $Y - 0.397776982902 * $Z;
        $Z0 = 0.397776982902 * $Y + 0.917482137087 * $Z;

        return new RectangularCoordinates($X0, $Y0, $Z0);
    }

    /**
     * Calculates the equation of time of the sun for a given date.
     *
     * @param  Carbon  $date  The date
     * @return float The equation of time in minutes
     *
     * See chapter 28 of Astronomical Algorithms
     */
    public function calculateEquationOfTime(Carbon $date): float
    {
        $tau = (Time::getJd($date) - 2451545.0) / 365250.0;

        $L0 = new Coordinate(280.4664567 + 360007.6982779 * $tau
                 + 0.03032028 * $tau ** 2 + $tau ** 3 / 49931
                 - $tau ** 4 / 15300 - $tau ** 5 / 2000000, 0, 360);

        $nutation = Time::nutation(Time::getJd($date));

        $this->calculateEquatorialCoordinatesHighAccuracy($date, $nutation);
        $ra = $this->getEquatorialCoordinates()->getRA()->getCoordinate() * 15.0;

        $E = $L0->getCoordinate() - 0.0057183 - $ra + $nutation[0] / 3600.0 * cos(deg2rad($nutation[3]));

        if ($E > 180) {
            $E = $E - 360;
        }

        return $E * 4;
    }

    /**
     * Calculates the ephemeris for physical observations of the sun.
     *
     * @param  Carbon  $date  The date
     * @param  float  $deltaT  Delta T for the given date
     * @return array The ephemeris for physical observations of the sun.
     *               First element: P: The position angle of the northern
     *               extremity of the axis of rotation, measured
     *               eastwards from the North Point of the solar disk.
     *               Second element: B0: The heliographic latitude of the center of the solar disk
     *               Third element:  L0: The heliographic ongitude of the same point
     *
     * See chapter 29 of Astronomical Algorithms
     */
    public function getPhysicalEphemeris(Carbon $date, float $deltaT): array
    {
        $jd = Time::getJd($date) + $deltaT / 86400.0;

        $theta = (new Coordinate(($jd - 2398220) * 360 / 25.38))->getCoordinate();
        $I = 7.25;
        $K = 73.6667 + 1.3958333 * ($jd - 2396758) / 36525;

        // tau = julian millenia since epoch J2000.0
        $tau = ($jd - 2451545.0) / 365250.0;

        $L = $this->_calculateL($tau);
        $R = $this->_calculateR($tau);

        $nutation = Time::nutation($jd);
        $deltaPsi = $nutation[0];
        $epsilon = $nutation[3];

        $lambda = $L + 180 - 20.4898 / $R / 3600;
        $lambda_accent = $lambda + $deltaPsi / 3600.0;

        $x = (new Coordinate(
            rad2deg(atan(-cos(deg2rad($lambda_accent)) * tan(deg2rad($epsilon)))),
            -90.0,
            90.0
        ))->getCoordinate();
        $y = (new Coordinate(
            rad2deg(atan(-cos(deg2rad($lambda - $K)) * tan(deg2rad($I)))),
            -90.0,
            90.0
        ))->getCoordinate();

        $P = $x + $y;
        $B0 = (new Coordinate(
            rad2deg(asin(sin(deg2rad($lambda - $K)) * sin(deg2rad($I)))),
            -90.0,
            90.0
        ))->getCoordinate();

        $eta = rad2deg(atan(tan(deg2rad($lambda - $K)) * cos(deg2rad($I))));

        $L0 = (new Coordinate($eta - $theta))->getCoordinate();

        return [$P, $B0, $L0];
    }

    /**
     * Calculate the diameter of the Sun.  You can get the diamter
     * by using the getDiameter method.
     *
     * @param  Carbon  $date  The date
     * @return None
     *
     * Chapter 55 of Astronomical Algorithms
     */
    public function calculateDiameter(Carbon $date)
    {
        $earth = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);
        $R = $helio_coords_earth[2];

        $this->setDiameter(round(2 * 959.63 / $R, 1));
    }
}
