<?php

/**
 * EquatorialCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Coordinates;

use Carbon\Carbon;

/**
 * EquatorialCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EquatorialCoordinates extends Coordinates
{
    private float $_ra;
    private float $_decl;

    /**
     * The constructor.
     *
     * @param float $ra          The right ascension (0, 24)
     * @param float $declination The declination (-90, 90)
     */
    public function __construct(float $ra, float $declination)
    {
        $this->setMinValue1(0.0);
        $this->setMaxValue1(24.0);
        $this->setMinValue2(-90.0);
        $this->setMaxValue2(90.0);

        $this->setRA($ra);
        $this->setDeclination($declination);
    }

    /**
     * Sets the right ascension.
     *
     * @param float $ra The right ascension
     *
     * @return None
     */
    public function setRA(float $ra): void
    {
        if ($ra < 0.0 || $ra > 24.0) {
            $ra = $this->bringInInterval1($ra);
        }
        $this->_ra = $ra;
    }

    /**
     * Sets the declination.
     *
     * @param float $declination The declination
     *
     * @return None
     */
    public function setDeclination(float $declination): void
    {
        if ($declination < -90.0 || $declination > 90.0) {
            $declination = $this->bringInInterval2($declination);
        }
        $this->_decl = $declination;
    }

    /**
     * Gets the Right Ascension.
     *
     * @return float the Right Ascension in decimal hours
     */
    public function getRA(): float
    {
        return $this->_ra;
    }

    /**
     * Gets the declination.
     *
     * @return float The declination in degrees
     */
    public function getDeclination(): float
    {
        return $this->_decl;
    }

    /**
     * Returns a readable string of the declination.
     *
     * @return string A readable string of the declination in degrees,
     *                minutes, seconds
     */
    public function printDeclination(): string
    {
        return $this->convertToDegrees($this->getDeclination());
    }

    /**
     * Returns a readable string of the Right Ascension.
     *
     * @return string A readable string of the right ascension in hours,
     *                minutes, seconds
     */
    public function printRA(): string
    {
        return $this->convertToHours($this->getRA());
    }

    /**
     * Converts the equatorial coordinates to ecliptical coordinates in
     * the current equinox.
     *
     * @param float $nutObliquity The nutation in obliquity
     *
     * @return EclipticalCoordinates The ecliptical coordinates
     */
    public function convertToEcliptical(float $nutObliquity): EclipticalCoordinates
    {
        $longitude = rad2deg(
            atan2(
                sin(deg2rad($this->_ra * 15.0)) *
                cos(deg2rad($nutObliquity)) + tan(deg2rad($this->_decl)) *
                sin(deg2rad($nutObliquity)),
                cos(deg2rad($this->_ra * 15.0))
            )
        );

        $latitude = rad2deg(
            asin(
                sin(deg2rad($this->_decl)) * cos(deg2rad($nutObliquity))
                - cos(deg2rad($this->_decl)) * sin(deg2rad($nutObliquity)) *
                sin(deg2rad($this->_ra * 15.0))
            )
        );

        return new EclipticalCoordinates($longitude, $latitude);
    }

    /**
     * Converts the equatorial coordinates to ecliptical coordinates in
     * the J2000 equinox.
     *
     * @return EclipticalCoordinates The ecliptical coordinates
     */
    public function convertToEclipticalJ2000(): EclipticalCoordinates
    {
        return $this->convertToEcliptical(23.4392911);
    }

    /**
     * Converts the equatorial coordinates to ecliptical coordinates in
     * the B1950 equinox.
     *
     * @return EclipticalCoordinates The ecliptical coordinates
     */
    public function convertToEclipticalB1950(): EclipticalCoordinates
    {
        return $this->convertToEcliptical(23.4457889);
    }

    /**
     * Converts the equatorial coordinates to local horizontal coordinates.
     *
     * @param GeographicalCoordinates $geo_coords    the geographical
     *                                               coordinates
     * @param Carbon                  $siderial_time the local siderial time
     *
     * @return HorizontalCoordinates The horizontal coordinates
     */
    public function convertToHorizontal(
        GeographicalCoordinates $geo_coords,
        Carbon $siderial_time
    ): HorizontalCoordinates {
        // Latitude of the observer
        $phi = $geo_coords->getLatitude();

        // Local hour angle = local siderial time - ra
        $sid = ((
            ($siderial_time->milliseconds / 1000.0) + $siderial_time->second
        ) / 60.0 + $siderial_time->minute) / 60 + $siderial_time->hour;

        $H = ($sid - $this->getRA()) * 15.0;

        $azimuth = rad2deg(
            atan2(
                sin(deg2rad($H)),
                cos(deg2rad($H)) * sin(deg2rad($phi))
                - tan(deg2rad($this->getDeclination())) * cos(deg2rad($phi))
            )
        );

        $height = rad2deg(
            asin(
                sin(deg2rad($phi)) * sin(deg2rad($this->getDeclination()))
                + cos(deg2rad($phi)) * cos(deg2rad($this->getDeclination()))
                * cos(deg2rad($H))
            )
        );

        return new HorizontalCoordinates($azimuth, $height);
    }

    /**
     * Converts the equatorial coordinates to galactic coordinates.
     *
     * @return GalacticCoordinates The galactic coordinates
     */
    public function convertToGalactic(): GalacticCoordinates
    {
        $ra = $this->getRA() * 15.0;
        $decl = $this->getDeclination();

        $l = rad2deg(
            atan2(
                cos(deg2rad($decl)) * sin(deg2rad($ra - 192.85948)),
                sin(deg2rad($decl)) * cos(deg2rad(27.12825))
                - cos(deg2rad($decl)) * sin(deg2rad(27.12825))
                * cos(deg2rad($ra - 192.85948))
            )
        );

        $b = rad2deg(
            asin(
                sin(deg2rad($decl)) * sin(deg2rad(27.12825)) +
                cos(deg2rad($decl)) * cos(deg2rad(27.12825))
                * cos(deg2rad($ra - 192.85948))
            )
        );

        return new GalacticCoordinates(122.93192 - $l, $b);
    }
}
