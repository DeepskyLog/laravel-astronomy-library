<?php

/**
 * EclipticalCoordinates class.
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
 * EclipticalCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EclipticalCoordinates
{
    private Coordinate $_longitude;
    private Coordinate $_latitude;

    /**
     * The constructor.
     *
     * @param float $longitude The ecliptical longitude (0, 360)
     * @param float $latitude  The ecliptical latitude (-90, 90)
     */
    public function __construct(float $longitude, float $latitude)
    {
        $this->_longitude = new Coordinate($longitude);
        $this->_latitude = new Coordinate($latitude, -90.0, 90.0);
    }

    /**
     * Sets the ecliptical longitude.
     *
     * @param float $longitude The ecliptical longitude
     *
     * @return None
     */
    public function setLongitude(float $longitude): void
    {
        $this->_longitude->setCoordinate($longitude);
    }

    /**
     * Sets the ecliptical latitude.
     *
     * @param float $latitude The ecliptical latitude
     *
     * @return None
     */
    public function setLatitude(float $latitude): void
    {
        $this->_latitude->setCoordinate($latitude);
    }

    /**
     * Gets the ecliptical latitude.
     *
     * @return Coordinate the ecliptical latitude
     */
    public function getLatitude(): Coordinate
    {
        return $this->_latitude;
    }

    /**
     * Gets the ecliptical longitude.
     *
     * @return Coordinate The ecliptical longitude in degrees
     */
    public function getLongitude(): Coordinate
    {
        return $this->_longitude;
    }

    /**
     * Returns a readable string of the ecliptical longitude.
     *
     * @return string A readable string of the ecliptical longitude in degrees,
     *                minutes, seconds
     */
    public function printLongitude(): string
    {
        return $this->getLongitude()->convertToDegrees();
    }

    /**
     * Returns a readable string of the ecliptical latitude.
     *
     * @return string A readable string of the ecliptical latitude in degrees,
     *                minutes, seconds
     */
    public function printLatitude(): string
    {
        return $this->getLatitude()->convertToDegrees();
    }

    /**
     * Converts the ecliptical coordinates to equatorial coordinates.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @param float $nutObliquity The nutation in obliquity
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function convertToEquatorial(float $nutObliquity): EquatorialCoordinates
    {
        $ra = rad2deg(
            atan2(
                sin(deg2rad($this->_longitude->getCoordinate())) *
                cos(deg2rad($nutObliquity))
                - tan(deg2rad($this->_latitude->getCoordinate())) *
                sin(deg2rad($nutObliquity)),
                cos(deg2rad($this->_longitude->getCoordinate()))
            )
        );

        $decl = rad2deg(
            asin(
                sin(deg2rad($this->_latitude->getCoordinate()))
                * cos(deg2rad($nutObliquity))
                + cos(deg2rad($this->_latitude->getCoordinate()))
                * sin(deg2rad($nutObliquity))
                * sin(deg2rad($this->_longitude->getCoordinate()))
            )
        );

        return new EquatorialCoordinates($ra / 15.0, $decl);
    }

    /**
     * Converts the ecliptical coordinates to equatorial coordinates in
     * the J2000 equinox.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function convertToEquatorialJ2000(): EquatorialCoordinates
    {
        return $this->convertToEquatorial(23.4392911);
    }

    /**
     * Converts the ecliptical coordinates to equatorial coordinates in
     * the B1950 equinox.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function convertToEquatorialB1950(): EquatorialCoordinates
    {
        return $this->convertToEquatorial(23.4457889);
    }
}
