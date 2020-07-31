<?php

/**
 * HorizontalCoordinates class.
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
 * HorizontalCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class HorizontalCoordinates
{
    private Coordinate $_azimuth;
    private Coordinate $_h;

    /**
     * The constructor.
     *
     * @param float $azimuth  The azimuth, measured westwards from the South (0, 360)
     * @param float $altitude The altitude, positive above the horizon (-90, 90)
     */
    public function __construct(float $azimuth, float $altitude)
    {
        $this->setAzimuth($azimuth);
        $this->setAltitude($altitude);
    }

    /**
     * Sets the azimuth.
     *
     * @param float $azimuth The azimuth, measured westwards from the South (0, 360)
     *
     * @return None
     */
    public function setAzimuth(float $azimuth): void
    {
        $this->_azimuth = new Coordinate($azimuth);
    }

    /**
     * Sets the altitude.
     *
     * @param float $altitude The altitude above the horizon
     *
     * @return None
     */
    public function setAltitude(float $altitude): void
    {
        $this->_h = new Coordinate($altitude, -90.0, 90.0);
    }

    /**
     * Gets the azimuth.
     *
     * @return Coordinate the azimuth, measured westwards from the south
     */
    public function getAzimuth(): Coordinate
    {
        return $this->_azimuth;
    }

    /**
     * Gets the altitude.
     *
     * @return Coordinate The altitude above the horizon
     */
    public function getAltitude(): Coordinate
    {
        return $this->_h;
    }

    /**
     * Returns a readable string of the azimuth.
     *
     * @return string A readable string of the azimuth in degrees,
     *                minutes, seconds
     */
    public function printAzimuth(): string
    {
        return $this->getAzimuth()->convertToDegrees();
    }

    /**
     * Returns a readable string of the altitude above the horizon.
     *
     * @return string A readable string of the altitude above the horizon in degrees,
     *                minutes, seconds
     */
    public function printAltitude(): string
    {
        return $this->getAltitude()->convertToDegrees();
    }

    /**
     * Converts the local horizontal coordinates to equatorial coordinates.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @param GeographicalCoordinates $geo_coords    the geographical
     *                                               coordinates
     * @param Carbon                  $siderial_time the local siderial time
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function convertToEquatorial(
        GeographicalCoordinates $geo_coords,
        Carbon $siderial_time
    ): EquatorialCoordinates {
        // Latitude of the observer
        $phi = $geo_coords->getLatitude()->getCoordinate();

        // Local hour angle = local siderial time - ra
        $sid = ((
            ($siderial_time->milliseconds / 1000.0) + $siderial_time->second
        ) / 60.0 + $siderial_time->minute) / 60 + $siderial_time->hour;

        $H = rad2deg(
            atan2(
                sin(deg2rad($this->getAzimuth()->getCoordinate())),
                cos(deg2rad($this->getAzimuth()->getCoordinate()))
                * cos(deg2rad($phi))
                + tan(deg2rad($this->getAltitude()->getCoordinate()))
                * cos(deg2rad($phi))
            )
        );

        $declination = rad2deg(
            asin(
                sin(deg2rad($phi))
                * sin(deg2rad($this->getAltitude()->getCoordinate()))
                - cos(deg2rad($phi))
                * cos(deg2rad($this->getAltitude()->getCoordinate()))
                * cos(deg2rad($this->getAzimuth()->getCoordinate()))
            )
        );

        // a = altitude, A = Azimuth
        $x = cos(deg2rad($this->getAltitude()->getCoordinate()))
            * sin(deg2rad($this->getAzimuth()->getCoordinate()));

        $y = (
            sin(deg2rad($phi))
                * cos(deg2rad($this->getAltitude()->getCoordinate()))
                * cos(
                    deg2rad($this->getAzimuth()->getCoordinate())
                ) + cos(
                    deg2rad($phi)
                ) * sin(deg2rad($this->getAltitude()->getCoordinate()))
        );

        $H = rad2deg(
            atan2(
                $x,
                $y
            )
        );

        $ra = $sid - $H / 15.0;

        return new EquatorialCoordinates($ra, $declination);
    }

    /**
     * Calculates the refaction (in minutes of arc) if the apparent
     * height is given.
     * Chapter 16 of Astronomical Algorithms.
     *
     * @return float the refraction in minutes of arc
     */
    public function calculateRefractionFromApparentAltitude(): float
    {
        return 1 / (
            tan(
                deg2rad(
                    $this->getAltitude()->getCoordinate()
                    + 7.31 / ($this->getAltitude()->getCoordinate() + 4.4)
                )
            )
        );
    }

    /**
     * Calculates the refaction (in minutes of arc) if the true
     * height is given.
     * Chapter 16 of Astronomical Algorithms.
     *
     * @return float the refraction in minutes of arc
     */
    public function calculateRefractionFromTrueAltitude(): float
    {
        return 1.02 / (
            tan(
                deg2rad(
                    $this->getAltitude()->getCoordinate()
                    + 10.3 / ($this->getAltitude()->getCoordinate() + 5.11)
                )
            )
        );
    }
}
