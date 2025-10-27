<?php

/**
 * Procedures to work with times.
 *
 * PHP Version 8
 *
 * @category Time
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Models\DeltaT;
use Exception;

/**
 * Procedures to work with times.
 *
 * PHP Version 8
 *
 * @category Time
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
class Time
{
    /**
     * Calculates the julian day if the time is given.
     * Chapter 7 in Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date, in the correct timezone
     * @return float the julian day
     */
    public static function getJd(Carbon $date): float
    {
        // Get the time in UTC
        $date->setTimezone('UTC');

        $day = (($date->second / 60 + $date->minute) / 60 + $date->hour) / 24
            + $date->day;

        $month = $date->month;
        $year = $date->year;

        if ($month <= 2) {
            $year = --$year;
            $month = $month + 12;
        }

        if ($date < Carbon::create(1582, 10, 4, 0, 0, 0, 'UTC')) {
            $b = 0;
        } else {
            $a = (int) ($year / 100);
            $b = 2 - $a + (int) ($a / 4);
        }

        if ($date > Carbon::create(1582, 10, 4, 0, 0, 0, 'UTC')
            && $date < Carbon::create(1582, 10, 15, 0, 0, 0, 'UTC')
        ) {
            throw new InvalidDateException(
                'Date does not exist',
                $date
            );
        }
        if ($date < Carbon::create(-4712, 1, 1, 12, 0, 0, 'UTC')) {
            throw new InvalidDateException(
                'Date does not exist',
                $date
            );
        }

        return floor(365.25 * ($year + 4716)) +
            floor(30.6001 * ($month + 1)) + $day
            + $b - 1524.5;
    }

    /**
     * Calculates the carbon date is the julian day is given.
     * Chapter 7 in Astronomical Algorithms.
     *
     * @param  float  $jd  the julian day
     * @return Carbon The date, in the UTC timezone
     */
    public static function fromJd(float $jd): Carbon
    {
        if ($jd < 0.0) {
            throw new InvalidDateException(
                'Julian Day does not exist',
                $jd
            );
        }

        $jd = $jd + 0.5;
        $z = (int) $jd;
        $f = $jd - $z;

        if ($z < 2299161.0) {
            $a = $z;
        } else {
            $alpha = floor(($z - 1867216.25) / 36524.25);
            $a = $z + 1 + $alpha - floor($alpha / 4);
        }

        $b = $a + 1524;

        $c = floor(($b - 122.1) / 365.25);

        $d = floor(365.25 * $c);

        $e = floor(($b - $d) / 30.6001);

        $day = $b - $d - floor(30.6001 * $e);

        $hour = (int) ($f * 24);
        $decimalMinute = (($f * 24) - $hour) * 60;
        $minute = (int) $decimalMinute;
        $second = (int) (($decimalMinute - $minute) * 60);

        if ($e < 14) {
            $month = (int) ($e - 1);
        } else {
            $month = (int) ($e - 13);
        }

        if ($month > 2) {
            $year = (int) ($c - 4716);
        } else {
            $year = (int) ($c - 4715);
        }

        return Carbon::create($year, $month, $day, $hour, $minute, $second, 'UTC');
    }

    /**
     * Returns the dynamical time as the time + delta t.
     * Chapter 10 in Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date
     * @return Carbon The dynamical time
     */
    public static function dynamicalTime(Carbon $date): Carbon
    {
        return $date->addSeconds(self::deltaT($date));
    }

    /**
     * Calculates delta t for the given date.
     * Chapter 10 in Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date
     * @return float delta t in seconds
     */
    public static function deltaT(Carbon $date): float
    {
        $y = $date->year + ($date->month - 0.5) / 12;

        try {
            $databaseDate = Carbon::create(
                DeltaT::first()['year'] + 1,
                1,
                1,
                0,
                0,
                0,
                'UTC'
            );
            $databaseAvailable = true;
        } catch (\Throwable $e) {
            // Eloquent/DB may not be available in CLI contexts; fall back
            // to polynomial approximations below.
            $databaseAvailable = false;
        }

        if ($date < Carbon::create(-500, 1, 1, 12, 12, 12, 'UTC')) {
            $u = ($y - 1820) / 100;

            $deltaT = (int) (-20 + 32 * ($u ** 2));
        } elseif ($date < Carbon::create(500, 1, 1, 12, 12, 12, 'UTC')) {
            $u = $y / 100;

            $deltaT = (int) (10583.6 - 1014.41 * $u
                + 33.78311 * ($u ** 2)
                - 5.952053 * ($u ** 3)
                - 0.1798452 * ($u ** 4)
                + 0.022174192 * ($u ** 5)
                + 0.0090316521 * ($u ** 6));
        } elseif ($date < Carbon::create(1600, 1, 1, 12, 12, 12, 'UTC')) {
            $u = ($y - 1000) / 100;

            $deltaT = (int) (1574.2 - 556.01 * $u
                + 71.23472 * ($u ** 2)
                + 0.319781 * ($u ** 3)
                - 0.8503463 * ($u ** 4)
                - 0.005050998 * ($u ** 5)
                 + 0.0083572073 * ($u ** 6));
        } elseif ($date < Carbon::create(1620, 1, 1, 12, 12, 12, 'UTC')) {
            $t = $y - 1600;

            $deltaT = (int) (
                120 - 0.9808 * $t - 0.01532 * ($t ** 2) + ($t ** 3) / 7129
            );
        } elseif ($databaseAvailable && $date < $databaseDate) {
            $databaseEntry = DeltaT::where('year', '=', $date->year)->first();

            return $databaseEntry['deltat'];
        } elseif ($date < Carbon::create(2050, 1, 1, 12, 12, 12, 'UTC')) {
            $t = $y - 2000;

            $deltaT = 62.92 + 0.32217 * $t + 0.005589 * $t ** 2;
        } elseif ($date < Carbon::create(2150, 1, 1, 12, 12, 12, 'UTC')) {
            $deltaT = -20 + 32 * (($y - 1820) / 100) ** 2 - 0.5628 * (2150 - $y);
        } else {
            $u = ($y - 1820) / 100;

            $deltaT = -20 + 32 * $u ** 2;
        }

        return $deltaT;
    }

    /**
     * Calculates the mean siderial time for the given date.
     * Chapter 12 in Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date
     * @param  GeographicalCoordinates  $coords  The geographical coordinates
     * @return Carbon the siderial time
     */
    public static function meanSiderialTime(
        Carbon $date,
        GeographicalCoordinates $coords
    ): Carbon {
        $jd = self::getJd($date);
        $T = ($jd - 2451545.0) / 36525;

        $theta0 = 280.46061837
            + 360.98564736629 * ($jd - 2451545.0)
            + 0.000387933 * $T ** 2
            - $T ** 3 / 38710000;

        // Add the extra hours for the longitude
        $theta0 += $coords->getLongitude()->getCoordinate();

        // Bring $theta0 in the 0 - 360.0 interval
        $theta0 -= floor($theta0 / 360.0) * 360;

        $decimalHours = $theta0 / 15.0;
        $hour = (int) $decimalHours;
        $decimalMinutes = ($decimalHours - $hour) * 60.0;
        $minutes = (int) $decimalMinutes;
        $seconds = ($decimalMinutes - $minutes) * 60.0;

        return Carbon::create(
            $date->year,
            $date->month,
            $date->day,
            $hour,
            $minutes,
            $seconds,
            'UTC'
        );
    }

    /**
     * Calculates the apparent siderial time for the given date.
     * Chapter 12 in Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date
     * @param  GeographicalCoordinates  $coords  The geographical coordinates
     * @param  array|null  $nutation  The nutation array
     * @return Carbon the siderial time
     */
    public static function apparentSiderialTime(
        Carbon $date,
        GeographicalCoordinates $coords,
        ?array $nutation = null
    ): Carbon {
        $date = $date->copy()->timezone('UTC');
        $siderialTime = self::meanSiderialTime($date, $coords);
        if (! $nutation) {
            $jd = self::getJd($date);

            $nutation = self::nutation($jd);
        }
        $correction = cos(deg2rad($nutation[3])) * $nutation[0] / 15.0;
        $correction *= 1000000.0;

        $siderialTime->microsecond((int) ($siderialTime->microsecond + $correction));

        return $siderialTime;
    }

    /**
     * Calculates the apparent siderial time for the given date, at midnight,
     * in Greenwich.
     * Chapter 12 in Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date
     * @return Carbon the siderial time
     */
    public static function apparentSiderialTimeGreenwich(
        Carbon $date
    ): Carbon {
        $newDate = $date->copy()->timezone('UTC');
        $newDate->hour = 0;
        $newDate->minute = 0;
        $newDate->second = 0;
        $greenwich = new GeographicalCoordinates(0.0, 51.476852);

        return self::apparentSiderialTime($newDate, $greenwich);
    }

    /**
     * Calculates the nutation for the given julian day.
     * Chapter 22 of Astronomical Algorithms.
     *
     * @param  float  $jd  The Julian day
     * @return array The array with nutation in Longitude, nutation in Obliquity,
     *               mean Obliquity and true Obliquity
     */
    public static function nutation(float $jd): array
    {
        $T = ($jd - 2451545.0) / 36525.0;

        /* D stands for mean elongation of the moon from the sun. */
        $D = 297.85036 + 445267.111480 * $T - 0.0019142 * pow($T, 2) + pow($T, 3)
            / 189474.0;
        $D -= floor($D / 360.0) * 360;

        /* M stands for mean anomaly of the sun */
        $M = 357.52772 + 35999.050340 * $T - 0.0001603 * pow($T, 2) - pow($T, 3) /
            300000.0;
        $M -= floor($M / 360.0) * 360;

        /* M_accent stands for mean anomaly of the moon */
        $M_accent = 134.96298 + 477198.867398 * $T + 0.0086972 * pow($T, 2) +
            pow($T, 3) / 56250.0;
        $M_accent -= floor($M_accent / 360.0) * 360;

        /* F stands for the moon's argument of latitude */
        $F = 93.27191 + 483202.017538 * $T - 0.0036825 * pow($T, 2) + pow($T, 3) /
            327270.0;
        $F -= floor($F / 360.0) * 360;

        /* Omega stands for the longitude of the ascending node of the moon's
            mean orbit on the ecliptic, measured from the mean equinox of the date
        */
        $omega = 125.04452 - 1934.136261 * $T + 0.0020708 * pow($T, 2)
            + pow($T, 3) / 450000.0;
        $omega -= floor($omega / 360.0) * 360;

        // This is a very accurate calculation of the nutation in longitude
        $nutLongitude = (-171996.0 - 174.2 * $T) * sin(deg2rad($omega))
            + (-13187 - 1.6 * $T) * sin(deg2rad(-2 * $D + 2 * $F + 2 * $omega))
            + (-2274 - 0.2 * $T) * sin(deg2rad(2 * $F + 2 * $omega))
            + (2062 + 0.2 * $T) * sin(deg2rad(2 * $omega))
            + (1426 - 3.4 * $T) * sin(deg2rad($M))
            + (712 + 0.1 * $T) * sin(deg2rad($M_accent))
            + (-517 + 1.2 * $T) * sin(deg2rad(-2 * $D + $M + 2 * $F + 2 * $omega))
            + (-386 - 0.4 * $T) * sin(deg2rad(2 * $F + $omega))
            + (-301) * sin(deg2rad($M_accent + 2 * $F + 2 * $omega))
            + (217 - 0.5 * $T) * sin(deg2rad(-2 * $D - $M + 2 * $F + 2 * $omega))
            + (-158) * sin(deg2rad(-2 * $D + $M_accent))
            + (129 + 0.1 * $T) * sin(deg2rad(-2 * $D + 2 * $F + $omega))
            + 123 * sin(deg2rad(-$M_accent + 2 * $F + 2 * $omega))
            + 63 * sin(deg2rad(2 * $D))
            + (63 + 0.1 * $T) * sin(deg2rad($M_accent + $omega))
            + (-59) * sin(deg2rad(2 * $D - $M_accent + 2 * $F + 2 * $omega))
            + (-58 - 0.1 * $T) * sin(deg2rad(-$M_accent + $omega))
            + (-51) * sin(deg2rad($M_accent + 2 * $F + $omega))
            + 48 * sin(deg2rad(-2 * $D + 2 * $M_accent))
            + 46 * sin(deg2rad(-2 * $M_accent + 2 * $F + $omega))
            + (-38) * sin(deg2rad(2 * $D + 2 * $F + 2 * $omega))
            + (-31) * sin(deg2rad(2 * $M_accent + 2 * $F + 2 * $omega))
            + 29 * sin(deg2rad(2 * $M_accent))
            + 29 * sin(deg2rad(-2 * $D + $M_accent + 2 * $F + 2 * $omega))
            + 26 * sin(deg2rad(2 * $F))
            + (-22) * sin(deg2rad(-2 * $D + 2 * $F))
            + 21 * sin(deg2rad(-$M_accent + 2 * $F + $omega))
            + (17 - 0.1 * $T) * sin(deg2rad(2 * $M))
            + 16 * sin(deg2rad(2 * $D - $M_accent + $omega))
            + (-16 + 0.1 * $T) * sin(deg2rad(-2 * $D + 2 * $M + 2 * $F + 2 * $omega))
            + (-15) * sin(deg2rad($M + $omega))
            + (-13) * sin(deg2rad(-2 * $D + $M_accent + $omega))
            + (-12) * sin(deg2rad(-$M + $omega))
            + 11 * sin(deg2rad(2 * $M_accent - 2 * $F))
            + (-10) * sin(deg2rad(2 * $D - $M_accent + 2 * $F + $omega))
            + (-8) * sin(deg2rad(2 * $D + $M_accent + 2 * $F + 2 * $omega))
            + 7 * sin(deg2rad($M + 2 * $F + 2 * $omega))
            + (-7) * sin(deg2rad(-2 * $D + $M + $M_accent))
            + (-7) * sin(deg2rad(-$M + 2 * $F + 2 * $omega))
            + (-7) * sin(deg2rad(2 * $D + 2 * $F + $omega))
            + 6 * sin(deg2rad(2 * $D + $M_accent))
            + 6 * sin(deg2rad(-2 * $D + 2 * $M_accent + 2 * $F + 2 * $omega))
            + 6 * sin(deg2rad(-2 * $D + $M_accent + 2 * $F + $omega))
            + (-6) * sin(deg2rad(2 * $D - 2 * $M_accent + $omega))
            + (-6) * sin(deg2rad(2 * $D + $omega))
            + 5 * sin(deg2rad(-$M + $M_accent))
            + (-5) * sin(deg2rad(-2 * $D - $M + 2 * $F + $omega))
            + (-5) * sin(deg2rad(-2 * $D + $omega))
            + (-5) * sin(deg2rad(2 * $M_accent + 2 * $F + $omega))
            + 4 * sin(deg2rad(-2 * $D + 2 * $M_accent + $omega))
            + 4 * sin(deg2rad(-2 * $D + $M + 2 * $F + $omega))
            + 4 * sin(deg2rad($M_accent - 2 * $F))
            + (-4) * sin(deg2rad(-$D + $M_accent))
            + (-4) * sin(deg2rad(-2 * $D + $M))
            + (-4) * sin(deg2rad($D))
            + 3 * sin(deg2rad($M_accent + 2 * $F))
            + (-3) * sin(deg2rad(-2 * $M_accent + 2 * $F + 2 * $omega))
            + (-3) * sin(deg2rad(-$D - $M + $M_accent))
            + (-3) * sin(deg2rad($M + $M_accent))
            + (-3) * sin(deg2rad(-$M + $M_accent + 2 * $F + 2 * $omega))
            + (-3) * sin(deg2rad(2 * $D - $M - $M_accent + 2 * $F + 2 * $omega))
            + (-3) * sin(deg2rad(3 * $M_accent + 2 * $F + 2 * $omega))
            + (-3) * sin(deg2rad(2 * $D - $M + 2 * $F + 2 * $omega));

        $nutLongitude /= 10000.0;

        // This is a very accurate calculation of the nutation in longitude
        $nutObliquity = (92025.0 + 8.9 * $T) * cos(deg2rad($omega))
                + (5736 - 3.1 * $T) * cos(deg2rad(-2 * $D + 2 * $F + 2 * $omega))
                + (977 - 0.5 * $T) * cos(deg2rad(2 * $F + 2 * $omega))
                + (-895 + 0.5 * $T) * cos(deg2rad(2 * $omega))
                + (54 - 0.1 * $T) * cos(deg2rad($M))
                + (-7) * cos(deg2rad($M_accent))
                + (224 - 0.6 * $T) * cos(deg2rad(-2 * $D + $M + 2 * $F + 2 * $omega))
                + 200 * cos(deg2rad(2 * $F + $omega))
                + (129 - 0.1 * $T) * cos(deg2rad($M_accent + 2 * $F + 2 * $omega))
                + (-95 + 0.3 * $T) * cos(deg2rad(-2 * $D - $M + 2 * $F + 2 * $omega))
                + (-70) * cos(deg2rad(-2 * $D + 2 * $F + $omega))
                + (-53) * cos(deg2rad(-$M_accent + 2 * $F + 2 * $omega))
                + (-33) * cos(deg2rad($M_accent + $omega))
                + 26 * cos(deg2rad(2 * $D - $M_accent + 2 * $F + 2 * $omega))
                + 32 * cos(deg2rad(-$M_accent + $omega))
                + 27 * cos(deg2rad($M_accent + 2 * $F + $omega))
                + (-24) * cos(deg2rad(-2 * $M_accent + 2 * $F + $omega))
                + 16 * cos(deg2rad(2 * $D + 2 * $F + 2 * $omega))
                + 13 * cos(deg2rad(2 * $M_accent + 2 * $F + 2 * $omega))
                + (-12) * cos(deg2rad(-2 * $D + $M_accent + 2 * $F + 2 * $omega))
                + (-10) * cos(deg2rad(-$M_accent + 2 * $F + $omega))
                + (-8) * cos(deg2rad(2 * $D - $M_accent + $omega))
                + 7 * cos(deg2rad(-2 * $D + 2 * $M + 2 * $F + 2 * $omega))
                + 9 * cos(deg2rad($M + $omega))
                + 7 * cos(deg2rad(-2 * $D + $M_accent + $omega))
                + 6 * cos(deg2rad(-$M + $omega))
                + 5 * cos(deg2rad(2 * $D - $M_accent + 2 * $F + $omega))
                + 3 * cos(deg2rad(2 * $D + $M_accent + 2 * $F + 2 * $omega))
                + (-3) * cos(deg2rad($M + 2 * $F + 2 * $omega))
                + 3 * cos(deg2rad(-$M + 2 * $F + 2 * $omega))
                + 3 * cos(deg2rad(2 * $D + 2 * $F + $omega))
                + (-3) * cos(deg2rad(-2 * $D + 2 * $M_accent + 2 * $F + 2 * $omega))
                + (-3) * cos(deg2rad(-2 * $D + $M_accent + 2 * $F + $omega))
                + 3 * cos(deg2rad(2 * $D - 2 * $M_accent + $omega))
                + 3 * cos(deg2rad(2 * $D + $omega))
                + 3 * cos(deg2rad(-2 * $D - $M + 2 * $F + $omega))
                + 3 * cos(deg2rad(-2 * $D + $omega))
                + 3 * cos(deg2rad(2 * $M_accent + 2 * $F + $omega));

        $nutObliquity /= 10000.0;

        $U = $T / 100.0;
        /* For the obliquity, we have an accuracy of 0.01 arc seconds after
           1000 years. (A.D. 1000 - 3000). The accuracy is still a few seconds of
           arc 10000 years after or before 2000 A.D. */
        $meanObliquity = (84381.448 - 4680.93 * $U
                            - 1.55 * pow($U, 2)
                            + 1999.25 * pow($U, 3)
                            - 51.38 * pow($U, 4)
                            - 249.67 * pow($U, 5)
                            - 39.05 * pow($U, 6)
                            + 7.12 * pow($U, 7)
                            + 27.87 * pow($U, 8)
                            + 5.79 * pow($U, 9)
                            + 2.45 * pow($U, 10)) / 3600.0;

        $trueObliquity = $meanObliquity + $nutObliquity / 3600.0;

        return [
            $nutLongitude, $nutObliquity, $meanObliquity, $trueObliquity,
        ];
    }

    /**
     * Returns the date of spring for the given year
     * Chapter 27 of Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date, with the year for which to calculate spring.
     * @return Carbon The correct time and date for spring
     */
    public static function getSpring(Carbon $date): Carbon
    {
        $year = $date->year;

        if ($year < 1000) {
            $Y = $year / 1000;
            $JDE0 = 1721139.29189 + 365242.13740 * $Y + 0.06134 * $Y ** 2 + 0.00111 * $Y ** 3 - 0.00071 * $Y ** 4;
        } else {
            $Y = ($year - 2000) / 1000;
            $JDE0 = 2451623.80984 + 365242.37404 * $Y + 0.05169 * $Y ** 2 - 0.00411 * $Y ** 3 - 0.00057 * $Y ** 4;
        }

        return Time::fromJd(Time::calculateSeasonTime($JDE0));
    }

    /**
     * Returns the date of summer for the given year
     * Chapter 27 of Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date, with the year for which to calculate summer.
     * @return Carbon The correct time and date for summer
     */
    public static function getSummer(Carbon $date): Carbon
    {
        $year = $date->year;

        if ($year < 1000) {
            $Y = $year / 1000;
            $JDE0 = 1721233.25401 + 365241.72562 * $Y - 0.05323 * $Y ** 2 + 0.00907 * $Y ** 3 + 0.00025 * $Y ** 4;
        } else {
            $Y = ($year - 2000) / 1000;
            $JDE0 = 2451716.56767 + 365241.62603 * $Y + 0.00325 * $Y ** 2 + 0.00888 * $Y ** 3 - 0.00030 * $Y ** 4;
        }

        return Time::fromJd(Time::calculateSeasonTime($JDE0));
    }

    /**
     * Returns the date of autumn for the given year
     * Chapter 27 of Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date, with the year for which to calculate autumn.
     * @return Carbon The correct time and date for autumn
     */
    public static function getAutumn(Carbon $date): Carbon
    {
        $year = $date->year;

        if ($year < 1000) {
            $Y = $year / 1000;
            $JDE0 = 1721325.70455 + 365242.49558 * $Y - 0.11677 * $Y ** 2 - 0.00297 * $Y ** 3 + 0.00074 * $Y ** 4;
        } else {
            $Y = ($year - 2000) / 1000;
            $JDE0 = 2451810.21715 + 365242.01767 * $Y - 0.11575 * $Y ** 2 + 0.00337 * $Y ** 3 + 0.00078 * $Y ** 4;
        }

        return Time::fromJd(Time::calculateSeasonTime($JDE0));
    }

    /**
     * Returns the date of winter for the given year
     * Chapter 27 of Astronomical Algorithms.
     *
     * @param  Carbon  $date  The date, with the year for which to calculate winter.
     * @return Carbon The correct time and date for winter
     */
    public static function getWinter(Carbon $date): Carbon
    {
        $year = $date->year;

        if ($year < 1000) {
            $Y = $year / 1000;
            $JDE0 = 1721414.39987 + 365242.88257 * $Y - 0.00769 * $Y ** 2 - 0.00933 * $Y ** 3 + 0.00006 * $Y ** 4;
        } else {
            $Y = ($year - 2000) / 1000;
            $JDE0 = 2451900.05952 + 365242.74049 * $Y - 0.06223 * $Y ** 2 - 0.00823 * $Y ** 3 + 0.00032 * $Y ** 4;
        }

        return Time::fromJd(Time::calculateSeasonTime($JDE0));
    }

    /**
     * Calculate the start of the season, given JDE0.
     */
    private static function calculateSeasonTime(float $JDE0): float
    {
        $T = ($JDE0 - 2451545.0) / 36525.0;
        $W = 35999.373 * $T - 2.47;
        $deltaLambda = 1 + 0.0334 * cos(deg2rad($W)) + 0.0007 * cos(deg2rad(2 * $W));

        $S = 485 * cos(deg2rad(324.96 + 1934.136 * $T))
        + 203 * cos(deg2rad(337.23 + 32964.467 * $T))
        + 199 * cos(deg2rad(342.08 + 20.186 * $T))
        + 182 * cos(deg2rad(27.85 + 445267.112 * $T))
        + 156 * cos(deg2rad(73.14 + 45036.886 * $T))
        + 136 * cos(deg2rad(171.52 + 22518.443 * $T))
        + 77 * cos(deg2rad(222.54 + 65928.934 * $T))
        + 74 * cos(deg2rad(296.72 + 3034.906 * $T))
        + 70 * cos(deg2rad(243.58 + 9037.513 * $T))
        + 58 * cos(deg2rad(119.81 + 33718.147 * $T))
        + 52 * cos(deg2rad(297.17 + 150.678 * $T))
        + 50 * cos(deg2rad(21.02 + 2281.226 * $T))
        + 45 * cos(deg2rad(247.54 + 29929.562 * $T))
        + 44 * cos(deg2rad(325.15 + 31555.956 * $T))
        + 29 * cos(deg2rad(60.93 + 4443.417 * $T))
        + 18 * cos(deg2rad(155.12 + 67555.328 * $T))
        + 17 * cos(deg2rad(288.79 + 4562.452 * $T))
        + 16 * cos(deg2rad(198.04 + 62894.029 * $T))
        + 14 * cos(deg2rad(199.76 + 31436.921 * $T))
        + 12 * cos(deg2rad(95.39 + 14577.848 * $T))
        + 12 * cos(deg2rad(287.11 + 31931.756 * $T))
        + 12 * cos(deg2rad(320.81 + 34777.259 * $T))
        + 9 * cos(deg2rad(227.73 + 1222.114 * $T))
        + 8 * cos(deg2rad(15.45 + 16859.074 * $T));

        return $JDE0 + 0.00001 * $S / $deltaLambda;
    }
}
