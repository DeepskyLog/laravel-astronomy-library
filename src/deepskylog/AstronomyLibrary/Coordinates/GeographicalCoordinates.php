<?php

/**
 * GeographicalCoordinates class.
 *
 * PHP Version 8
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Coordinates;

/**
 * GeographicalCoordinates class.
 *
 * PHP Version 8
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class GeographicalCoordinates
{
    private Coordinate $_longitude;
    private Coordinate $_latitude;

    /**
     * The constructor.
     *
     * @param float $longitude The geographical longitude (-180 ,180)
     * @param float $latitude  The geographical latitude (-90, 90)
     */
    public function __construct(float $longitude, float $latitude)
    {
        $this->setLongitude($longitude);
        $this->setLatitude($latitude);
    }

    /**
     * Sets the geographical longitude.
     *
     * @param float $longitude The geographical longitude
     *
     * @return None
     */
    public function setLongitude(float $longitude): void
    {
        $this->_longitude = new Coordinate($longitude, -180.0, 180.0);
    }

    /**
     * Sets the geographical latitude.
     *
     * @param float $latitude The geographical latitude
     *
     * @return None
     */
    public function setLatitude(float $latitude): void
    {
        $this->_latitude = new Coordinate($latitude, -90.0, 90.0);
    }

    /**
     * Gets the geographical longitude.
     *
     * @return Coordinate The geographical longitude
     */
    public function getLongitude(): Coordinate
    {
        return $this->_longitude;
    }

    /**
     * Gets the geographical latitude.
     *
     * @return Coordinate The geographical latitude
     */
    public function getLatitude(): Coordinate
    {
        return $this->_latitude;
    }

    /**
     * Returns a readable string of the latitude.
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes, seconds
     */
    public function printLatitude(): string
    {
        return $this->getLatitude()->convertToDegrees();
    }

    /**
     * Returns a readable string of the longitude.
     *
     * @return string A readable string of the coordinate in degrees,
     *                minutes, seconds
     */
    public function printLongitude(): string
    {
        return $this->getLongitude()->convertToDegrees();
    }

    /**
     * Returns rhoSinPhi and rhoCosPhi.
     * Needed for the calculation of the parallax.
     *
     * @param float $height The height of the location
     *
     * @return array with rhoSinPhi and rhoCosPhi
     *
     * See Chapter 11 of Astronomical Algorithms
     */
    public function earthsGlobe(float $height): array
    {
        $u = atan(0.99664719 * tan(deg2rad($this->getLatitude()->getCoordinate())));
        $rhoSinPhi = 0.99664719 * sin($u) + ($height / 6378140) * sin(deg2rad($this->getLatitude()->getCoordinate()));
        $rhoCosPhi = cos($u) + ($height / 6378140) * cos(deg2rad($this->getLatitude()->getCoordinate()));

        return [$rhoSinPhi, $rhoCosPhi];
    }
}
