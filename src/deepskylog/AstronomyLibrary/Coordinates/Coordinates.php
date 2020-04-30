<?php

/**
 * Abstract Coordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Coordinates;

/**
 * Abstract Coordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
abstract class Coordinates
{
    /**
     * Returns a readable string of the coordinate.
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes, seconds
     */
    abstract public function printLongitude(): string;

    /**
     * Returns a readable string of the coordinate.
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes, seconds
     */
    abstract public function printLatitude(): string;

    /**
     * Converts the coordinate to degrees°minutes'seconds''.
     *
     * @param float $coords The coordinates to print
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes, seconds
     */
    protected function convertToDegrees($coords): string
    {
        $sign = ' ';
        if ($coords < 0) {
            $sign = '-';
            $coords = -$coords;
        }
        $degrees = floor($coords);
        $subminutes = 60 * ($coords - $degrees);
        $minutes = floor($subminutes);
        $subseconds = 60 * ($subminutes - $minutes);
        $seconds = round($subseconds);
        if ($seconds == 60) {
            $seconds = 0;
            $minutes++;
        }
        if ($minutes == 60) {
            $minutes = 0;
            $degrees++;
        }

        return $sign . sprintf('%02d', $degrees) . '°'
        . sprintf('%02d', $minutes) . "'"
        . sprintf('%02d', $seconds) . '"';
    }

    /**
     * Converts the coordinate to hourshminutes'seconds''.
     *
     * @param float $coords The coordinates to print
     *
     * @return string A readable string of the coordinate in hours,
     *                minutes, seconds
     */
    protected function convertToHours($coords)
    {
        $degrees = floor($coords);
        $subminutes = 60 * ($coords - $degrees);
        $minutes = floor($subminutes);
        $subseconds = 60 * ($subminutes - $minutes);
        $seconds = round($subseconds);
        if ($seconds == 60) {
            $seconds = 0;
            $minutes++;
        }
        if ($minutes == 60) {
            $minutes = 0;
            $degrees++;
        }

        return sprintf('%02d', $degrees) . 'h'
        . sprintf('%02d', $minutes) . "'"
        . sprintf('%02d', $seconds) . '"';
    }
}
