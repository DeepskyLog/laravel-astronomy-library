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

use Illuminate\Support\Carbon;
use deepskylog\AstronomyLibrary\Time;

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
    private float $_epoch = 2000.0;
    private float $_deltaRA = 0.0;
    private float $_deltaDec = 0.0;

    /**
     * The constructor.
     *
     * @param float $longitude The ecliptical longitude (0, 360)
     * @param float $latitude  The ecliptical latitude (-90, 90)
     * @param float $epoch     The epoch of the target (2000.0 is standard)
     * @param float $deltaRA   The proper motion in Right Ascension in seconds/year
     *                         (in equatorial coordinates!)
     * @param float $deltaDec  The proper motion in declination in ''/year
     *                         (in equatorial coordinates!)
     */
    public function __construct(
        float $longitude,
        float $latitude,
        float $epoch = 2000.0,
        float $deltaRA = 0.0,
        float $deltaDec = 0.0
    ) {
        $this->setLongitude($longitude);
        $this->setLatitude($latitude);
        $this->setEpoch($epoch);
        $this->setDeltaRA($deltaRA);
        $this->setDeltaDec($deltaDec);
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
        $this->_longitude = new Coordinate($longitude);
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
        $this->_latitude = new Coordinate($latitude, -90.0, 90.0);
    }

    /**
     * Sets the epoch.
     *
     * @param float $epoch The epoch
     *
     * @return None
     */
    public function setEpoch(float $epoch): void
    {
        $this->_epoch = $epoch;
    }

    /**
     * Sets the proper motion in RA.
     *
     * @param float $deltaRA the proper motion in RA is seconds/year
     *                       (in equatorial coordinates!)
     *
     * @return None
     */
    public function setDeltaRA(float $deltaRA): void
    {
        $this->_deltaRA = $deltaRA;
    }

    /**
     * Sets the proper motion in declination.
     *
     * @param float $deltaDec the proper motion in declination in ''/year
     *                        (in equatorial coordinates!)
     *
     * @return None
     */
    public function setDeltaDec(float $deltaDec): void
    {
        $this->_deltaDec = $deltaDec;
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
     * Gets the epoch.
     *
     * @return float The epoch
     */
    public function getEpoch(): float
    {
        return $this->_epoch;
    }

    /**
     * Gets the the proper motion in RA.
     *
     * @return float The proper motion in RA in seconds/year
     *               (in equatorial coordinates!)
     */
    public function getDeltaRA(): float
    {
        return $this->_deltaRA;
    }

    /**
     * Gets the the proper motion in declination.
     *
     * @return float The proper motion in declination in ''/year
     *               (in equatorial coordinates!)
     */
    public function getDeltaDec(): float
    {
        return $this->_deltaDec;
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
