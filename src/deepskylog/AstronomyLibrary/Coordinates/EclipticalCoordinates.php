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

use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Carbon;

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

    /**
     * The constructor.
     *
     * @param float $longitude The ecliptical longitude (0, 360)
     * @param float $latitude  The ecliptical latitude (-90, 90)
     * @param float $epoch     The epoch of the target (2000.0 is standard)
     */
    public function __construct(
        float $longitude,
        float $latitude,
        float $epoch = 2000.0
    ) {
        $this->setLongitude($longitude);
        $this->setLatitude($latitude);
        $this->setEpoch($epoch);
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

    /**
     * Returns the precession: the coordinates for another epoch and equinox.
     * Chapter 21 of Astronomical Algorithms.
     *
     * @param Carbon $date The date for the new equinox
     *
     * @return EclipticalCoordinates the precessed coordinates
     */
    public function precessionHighAccuracy(Carbon $date): EclipticalCoordinates
    {
        $precessed_coordinates = clone $this;

        $epoch_in_JD = Time::getJd(
            Carbon::create($this->getEpoch(), 1, 1, 12, 0, 0, 'UTC')
        );

        $time_interval_J2000_starting = ($epoch_in_JD - 2451545.0) / 36525.0;

        $jd = Time::getJd($date);

        $time_interval_starting_final = ($jd - $epoch_in_JD) / 36525.0;

        $eta = (
            (
                47.0029
                - 0.06603 * $time_interval_J2000_starting
                + 0.000598 * $time_interval_J2000_starting ** 2
            ) * $time_interval_starting_final
            + (-0.03302 + 0.000598 * $time_interval_J2000_starting)
                * $time_interval_starting_final ** 2
            + 0.000060 * $time_interval_starting_final ** 3
        ) / 3600.0;

        $pi = (
            (
                174.876384 * 3600.0
                + 3289.4789 * $time_interval_J2000_starting
                + 0.60622 * $time_interval_J2000_starting ** 2
            )
            - (869.8089 + 0.50491 * $time_interval_J2000_starting)
                * $time_interval_starting_final
            + 0.03536 * $time_interval_starting_final ** 2
        ) / 3600.0;

        $rho = (
            (
                5029.0966
                + 2.22226 * $time_interval_J2000_starting
                - 0.000042 * $time_interval_J2000_starting ** 2
            ) * $time_interval_starting_final
            + (1.11113 - 0.000042 * $time_interval_J2000_starting)
                 * $time_interval_starting_final ** 2
            - 0.000006 * $time_interval_starting_final ** 3
        ) / 3600.0;

        $A_accent = cos(deg2rad($eta))
            * cos(deg2rad($this->getLatitude()->getCoordinate()))
            * sin(deg2rad($pi - $this->getLongitude()->getCoordinate()))
            - sin(deg2rad($eta))
            * sin(deg2rad($this->getLatitude()->getCoordinate()));

        $B_accent = cos(deg2rad($this->getLatitude()->getCoordinate()))
            * cos(deg2rad($pi - $this->getLongitude()->getCoordinate()));

        $C_accent = cos(deg2rad($eta))
            * sin(deg2rad($this->getLatitude()->getCoordinate()))
            + sin(deg2rad($eta))
            * cos(deg2rad($this->getLatitude()->getCoordinate()))
            * sin(deg2rad($pi - $this->getLongitude()->getCoordinate()));

        $precessed_coordinates->setLongitude(
            $rho + $pi - rad2deg(atan2($A_accent, $B_accent))
        );
        $precessed_coordinates->setLatitude(rad2deg(asin($C_accent)));

        return $precessed_coordinates;
    }
}
