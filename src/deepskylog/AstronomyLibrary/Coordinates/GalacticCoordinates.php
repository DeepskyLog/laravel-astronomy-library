<?php

/**
 * GalacticCoordinates class.
 *
 * PHP Version 8
 *
 * @category Coordinates
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Coordinates;

/**
 * GalacticCoordinates class.
 *
 * PHP Version 8
 *
 * @category Coordinates
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
class GalacticCoordinates
{
    private Coordinate $_longitude;
    private Coordinate $_latitude;

    /**
     * The constructor.
     *
     * @param  float  $longitude  The galactic longitude (0, 360)
     * @param  float  $latitude  The galactic latitude (-90, 90)
     */
    public function __construct(float $longitude, float $latitude)
    {
        $this->setLongitude($longitude);
        $this->setLatitude($latitude);
    }

    /**
     * Sets the galactic longitude.
     *
     * @param  float  $longitude  The galactic longitude
     * @return None
     */
    public function setLongitude(float $longitude): void
    {
        $this->_longitude = new Coordinate($longitude, 0.0, 360.0);
    }

    /**
     * Sets the galactic latitude.
     *
     * @param  float  $latitude  The galactic latitude
     * @return None
     */
    public function setLatitude(float $latitude): void
    {
        $this->_latitude = new Coordinate($latitude, -90.0, 90.0);
    }

    /**
     * Gets the galactic latitude.
     *
     * @return Coordainte the galactic latitude
     */
    public function getLatitude(): Coordinate
    {
        return $this->_latitude;
    }

    /**
     * Gets the galactic longitude.
     *
     * @return Coordinate The galactic longitude in degrees
     */
    public function getLongitude(): Coordinate
    {
        return $this->_longitude;
    }

    /**
     * Returns a readable string of the galactic longitude.
     *
     * @return string A readable string of the galactic longitude in degrees,
     *                minutes, seconds
     */
    public function printLongitude(): string
    {
        return $this->getLongitude()->convertToDegrees();
    }

    /**
     * Returns a readable string of the galactic latitude.
     *
     * @return string A readable string of the galactic latitude in degrees,
     *                minutes, seconds
     */
    public function printLatitude(): string
    {
        return $this->getLatitude()->convertToDegrees();
    }

    /**
     * Converts the galactic coordinates to equatorial coordinates.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function convertToEquatorial(): EquatorialCoordinates
    {
        $b = $this->getLatitude()->getCoordinate();
        $l = $this->getLongitude()->getCoordinate();

        $ra = rad2deg(
            atan2(
                cos(deg2rad($b)) * sin(deg2rad(122.93192 - $l)),
                sin(deg2rad($b)) * cos(deg2rad(27.12825))
                - cos(deg2rad($b)) * sin(deg2rad(27.12825))
                * cos(deg2rad(122.93192 - $l))
            )
        );

        $decl = rad2deg(
            asin(
                sin(deg2rad($b)) * sin(deg2rad(27.12825)) +
                cos(deg2rad($b)) * cos(deg2rad(27.12825))
                * cos(deg2rad(122.93192 - $l))
            )
        );

        $ra = $ra + 192.85948;

        return new EquatorialCoordinates($ra / 15.0, $decl);
    }
}
