<?php

/**
 * Coordinates class.
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
 * Coordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Coordinate
{
    private float $_minValue = 0.0;
    private float $_maxValue = 360.0;
    private float $_coordinate;

    /**
     * The constructor.
     *
     * @param float $coordinate The coordinate
     * @param float $minValue   The minimum value for the coordinate
     * @param float $maxValue   The maximum value for the coordinate
     */
    public function __construct(
        float $coordinate,
        float $minValue = 0.0,
        float $maxValue = 360.0
    ) {
        $this->setMinValue($minValue);
        $this->setMaxValue($maxValue);

        $this->setCoordinate($coordinate);
    }

    /**
     * Set the coordinate.
     *
     * @param float $coord the coordinate to set
     *
     * @return None
     */
    public function setCoordinate(float $coord): void
    {
        $this->_coordinate = $coord;
        $this->bringInInterval();
    }

    /**
     * Get the coordinate.
     *
     * @return float the coordinate
     */
    public function getCoordinate(): float
    {
        return $this->_coordinate;
    }

    /**
     * Converts the coordinate to degrees°minutes'seconds''.
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes, seconds
     */
    public function convertToDegrees(): string
    {
        $sign = ' ';
        $coords = $this->getCoordinate();
        if ($coords < 0) {
            $sign = '-';
            $coords = -$this->getCoordinate();
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
     * Converts the coordinate to degrees°minutes.
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes
     */
    public function convertToShortDegrees(): string
    {
        $sign = ' ';
        $coords = $this->getCoordinate();
        if ($coords < 0) {
            $sign = '-';
            $coords = -$this->getCoordinate();
        }
        $degrees = floor($coords);
        $subminutes = 60 * ($coords - $degrees);
        $minutes = floor($subminutes);

        if ($minutes == 60) {
            $minutes = 0;
            $degrees++;
        }

        return $sign.sprintf('%02d', $degrees).'°'
        .sprintf('%02d', $minutes)."'";
    }

    /**
     * Converts the coordinate to hms.
     *
     * @return string A readable string of the coordinate in hours,
     *                minutes, seconds
     */
    public function convertToHours(): string
    {
        $coords = $this->getCoordinate();

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
        .sprintf('%02d', $minutes).'m'
        .sprintf('%02d', $seconds).'s';
    }

    /**
     * Converts the coordinate to hm.
     *
     * @return string A readable string of the coordinate in hours,
     *                minutes
     */
    public function convertToShortHours(): string
    {
        $coords = $this->getCoordinate();

        $degrees = floor($coords);
        $subminutes = 60 * ($coords - $degrees);
        $minutes = floor($subminutes);

        if ($minutes == 60) {
            $minutes = 0;
            $degrees++;
        }

        return sprintf('%02d', $degrees).'h'
        .sprintf('%02d', $minutes).'m';
    }

    /**
     * Sets the minimum valid value for the coordinate.
     *
     * @param float $minValue The minimum valid value
     *
     * @return None
     */
    public function setMinValue(float $minValue): void
    {
        $this->_minValue = $minValue;
    }

    /**
     * Sets the maximum valid value for the first coordinate.
     *
     * @param float $maxValue The maximum valid value
     *
     * @return None
     */
    public function setMaxValue(float $maxValue): void
    {
        $this->_maxValue = $maxValue;
    }

    /**
     * Converts the coordinates to a coordinate in the required interval.
     *
     * @return None
     */
    public function bringInInterval(): void
    {
        $coord = $this->getCoordinate();
        $interval = $this->_maxValue - $this->_minValue;

        $coord = $coord - $this->_minValue;

        $this->_coordinate = $coord
            - floor($coord / $interval) * $interval + $this->_minValue;
    }
}
