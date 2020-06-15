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
class EquatorialCoordinates
{
    private Coordinate $_ra;
    private Coordinate $_decl;

    /**
     * The constructor.
     *
     * @param float $ra          The right ascension (0, 24)
     * @param float $declination The declination (-90, 90)
     */
    public function __construct(float $ra, float $declination)
    {
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
        $this->_ra = new Coordinate($ra, 0.0, 24.0);
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
        $this->_decl = new Coordinate($declination, -90.0, 90.0);
    }

    /**
     * Gets the Right Ascension.
     *
     * @return Coordinate the Right Ascension in decimal hours
     */
    public function getRA(): Coordinate
    {
        return $this->_ra;
    }

    /**
     * Gets the declination.
     *
     * @return Coordinate The declination in degrees
     */
    public function getDeclination(): Coordinate
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
        return $this->getDeclination()->convertToDegrees();
    }

    /**
     * Returns a readable string of the Right Ascension.
     *
     * @return string A readable string of the right ascension in hours,
     *                minutes, seconds
     */
    public function printRA(): string
    {
        return $this->getRA()->convertToHours();
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
                sin(deg2rad($this->_ra->getCoordinate() * 15.0)) *
                cos(deg2rad($nutObliquity))
                + tan(deg2rad($this->_decl->getCoordinate())) *
                sin(deg2rad($nutObliquity)),
                cos(deg2rad($this->_ra->getCoordinate() * 15.0))
            )
        );

        $latitude = rad2deg(
            asin(
                sin(deg2rad($this->_decl->getCoordinate()))
                * cos(deg2rad($nutObliquity))
                - cos(deg2rad($this->_decl->getCoordinate()))
                * sin(deg2rad($nutObliquity)) *
                sin(deg2rad($this->_ra->getCoordinate() * 15.0))
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
        $phi = $geo_coords->getLatitude()->getCoordinate();
        $H = $this->getHourAngle($siderial_time);

        $azimuth = rad2deg(
            atan2(
                sin(deg2rad($H)),
                cos(deg2rad($H)) * sin(deg2rad($phi))
                - tan(deg2rad($this->getDeclination()->getCoordinate()))
                * cos(deg2rad($phi))
            )
        );

        $height = rad2deg(
            asin(
                sin(deg2rad($phi))
                * sin(deg2rad($this->getDeclination()->getCoordinate()))
                + cos(deg2rad($phi))
                * cos(deg2rad($this->getDeclination()->getCoordinate()))
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
        $ra = $this->getRA()->getCoordinate() * 15.0;
        $decl = $this->getDeclination()->getCoordinate();

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

    /**
     * Returns the parallactic angle of the object. The parallactic angle is
     * negative before and positive after the passage throught the southern
     * meridian. This is the effect of the moon that is lying down at moonrise.
     * Astronomical Algorithms - chapter 13.
     *
     * @param GeographicalCoordinates $geo_coords    the geographical
     *                                               coordinates
     * @param Carbon                  $siderial_time the local siderial time
     *
     * @return float The parallactic angle q
     */
    public function getParallacticAngle(
        GeographicalCoordinates $geo_coords,
        Carbon $siderial_time
    ): float {
        $phi = $geo_coords->getLatitude()->getCoordinate();
        $H = $this->getHourAngle($siderial_time);

        $q = rad2deg(
            atan2(
                sin(deg2rad($H)),
                tan(deg2rad($phi)) * cos(deg2rad($this->getDeclination()->getCoordinate()))
                - sin(deg2rad($this->getDeclination()->getCoordinate())) * cos(deg2rad($H))
            )
        );

        return $q;
    }

    /**
     * Returns the local hour angle.
     *
     * @param Carbon $siderial_time The siderial time
     *
     * @return float the local hour angle
     */
    public function getHourAngle(Carbon $siderial_time): float
    {
        // Local hour angle = local siderial time - ra
        $sid = ((
            ($siderial_time->milliseconds / 1000.0) + $siderial_time->second
        ) / 60.0 + $siderial_time->minute) / 60 + $siderial_time->hour;

        return ($sid - $this->getRA()->getCoordinate()) * 15.0;
    }

    /**
     * Returns the angular separation between these coordinates and other
     * equatorial coordinates.
     *
     * @param EquatorialCoordinates $coords2 the coordinates of the second object
     *
     * @return Coordinate The angular separation between the two objects
     */
    public function angularSeparation(
        self $coords2
    ): Coordinate {
        $d = rad2deg(
            acos(
                sin(deg2rad($this->getDeclination()->getCoordinate())) *
                sin(deg2rad($coords2->getDeclination()->getCoordinate()))
                + cos(deg2rad($this->getDeclination()->getCoordinate())) *
                cos(deg2rad($coords2->getDeclination()->getCoordinate())) *
                cos(
                    deg2rad(
                        $this->getRA()->getCoordinate() * 15.0
                        - $coords2->getRA()->getCoordinate() * 15.0
                    )
                )
            )
        );

        if ($d < 0.16) {
            $d = sqrt(
                (
                    $this->getRA()->getCoordinate() * 15.0
                    - $coords2->getRA()->getCoordinate() * 15.0
                ) * cos(
                    deg2rad(
                        (
                            $this->getDeclination()->getCoordinate()
                            + $coords2->getDeclination()->getCoordinate()
                        ) / 2
                    )
                ) ** 2 +
                (
                    $this->getDeclination()->getCoordinate()
                    - $coords2->getDeclination()->getCoordinate()
                )
            );
        }

        return new Coordinate($d);
    }
}
