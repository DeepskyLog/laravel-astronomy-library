<?php

/**
 * Procedures to work with times.
 *
 * PHP Version 7
 *
 * @category Time
 * @package  AstronomyLibrary\Time
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary;

use Carbon\Carbon;

/**
 * Procedures to work with times.
 *
 * PHP Version 7
 *
 * @category Time
 * @package  AstronomyLibrary\Time
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Time
{
    /**
     * Calculates the julian day if the time is given.
     *
     * @param Carbon $date The date, in the correct timezone
     *
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
            $a = (int)($year / 100);
            $b = 2 - $a + (int)($a / 4);
        }

        if ($date > Carbon::create(1582, 10, 4, 0, 0, 0, 'UTC')
            && $date < Carbon::create(1582, 10, 15, 0, 0, 0, 'UTC')
        ) {
            throw new \Carbon\Exceptions\InvalidDateException(
                'Date does not exist',
                $date
            );
        }
        if ($date < Carbon::create(-4712, 1, 1, 12, 0, 0, 'UTC')) {
            throw new \Carbon\Exceptions\InvalidDateException(
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
     *
     * @param float $jd the julian day
     *
     * @return Carbon The date, in the UTC timezone
     */
    public static function fromJd(float $jd): Carbon
    {
        if ($jd < 0.0) {
            throw new \Carbon\Exceptions\InvalidDateException(
                'Julian Day does not exist',
                $jd
            );
        }

        $jd = $jd + 0.5;
        $z = (int)$jd;
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

        $hour = (int)($f * 24);
        $decimalMinute = (($f * 24) - $hour) * 60;
        $minute = (int)$decimalMinute;
        $second = (int)(($decimalMinute - $minute) * 60);

        if ($e < 14) {
            $month = (int)($e - 1);
        } else {
            $month = (int)($e - 13);
        }

        if ($month > 2) {
            $year = (int)($c - 4716);
        } else {
            $year = (int)($c - 4715);
        }

        return Carbon::create($year, $month, $day, $hour, $minute, $second, 'UTC');
    }
}
