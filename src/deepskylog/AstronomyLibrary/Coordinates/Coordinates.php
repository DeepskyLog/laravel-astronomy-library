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
    private float $_minValue1 = 0.0;
    private float $_maxValue1 = 360.0;
    private float $_minValue2 = 0.0;
    private float $_maxValue2 = 360.0;

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

        return $sign.sprintf('%02d', $degrees).'°'
        .sprintf('%02d', $minutes)."'"
        .sprintf('%02d', $seconds).'"';
    }

    /**
     * Converts the coordinate to hourshminutes'seconds''.
     *
     * @param float $coords The coordinates to print
     *
     * @return string A readable string of the coordinate in hours,
     *                minutes, seconds
     */
    protected function convertToHours($coords): string
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

        return sprintf('%02d', $degrees).'h'
        .sprintf('%02d', $minutes)."'"
        .sprintf('%02d', $seconds).'"';
    }

    /**
     * Sets the minimum valid value for the first coordinate.
     *
     * @param float $minValue The minimum valid value
     *
     * @return None
     */
    protected function setMinValue1(float $minValue): void
    {
        $this->_minValue1 = $minValue;
    }

    /**
     * Sets the maximum valid value for the first coordinate.
     *
     * @param float $maxValue The maximum valid value
     *
     * @return None
     */
    protected function setMaxValue1(float $maxValue): void
    {
        $this->_maxValue1 = $maxValue;
    }

    /**
     * Converts the coordinates to coordinates in the required interval.
     *
     * @return float the converted coordinate
     */
    protected function bringInInterval1(float $coord): float
    {
        $interval = $this->_maxValue1 - $this->_minValue1;

        $coord = $coord - $this->_minValue1;

        return $coord - floor($coord / $interval) * $interval + $this->_minValue1;
    }

    /**
     * Sets the minimum valid value for the second coordinate.
     *
     * @param float $minValue The minimum valid value
     *
     * @return None
     */
    protected function setMinValue2(float $minValue): void
    {
        $this->_minValue2 = $minValue;
    }

    /**
     * Sets the maximum valid value for the second coordinate.
     *
     * @param float $maxValue The maximum valid value
     *
     * @return None
     */
    protected function setMaxValue2(float $maxValue): void
    {
        $this->_maxValue2 = $maxValue;
    }

    /**
     * Converts the coordinates to coordinates in the required interval.
     *
     * @param float $coord the coordinate to bring the in the interval
     *
     * @return float the converted coordinate
     */
    protected function bringInInterval2(float $coord): float
    {
        $interval = $this->_maxValue2 - $this->_minValue2;

        $coord = $coord - $this->_minValue2;

        return $coord - floor($coord / $interval) * $interval + $this->_minValue2;
    }
}
