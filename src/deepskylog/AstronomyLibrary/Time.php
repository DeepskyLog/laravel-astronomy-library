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

        // TODO: Make sure to use julian or gregorian if needed
        // TODO: julian 4 October 1582 -> Gregorian: 15 October 1582
        if ($date < Carbon::create(1582, 10, 4, 0, 0, 0, 'UTC')) {
            $julianDay = gregoriantojd($date->month, $date->day, $date->year);
        } else {
            $julianDay = gregoriantojd($date->month, $date->day, $date->year);
        }

        $dayfrac = $date->hour / 24 - .5;
        if ($dayfrac < 0) {
            $dayfrac += 1;
        }

        //now set the fraction of a day
        $frac = $dayfrac + ($date->minute + $date->second / 60) / 60 / 24;

        $julianDay = $julianDay + $frac;

        return $julianDay;
    }
}
