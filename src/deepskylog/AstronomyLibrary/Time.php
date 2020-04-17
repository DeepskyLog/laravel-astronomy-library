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

        return floor(365.25 * ($year + 4716)) +
            floor(30.6001 * ($month + 1)) + $day
            + $b - 1524.5;
    }
}
