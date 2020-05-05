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
class HorizontalCoordinates extends Coordinates
{
    private float $_azimuth;
    private float $_h;

    /**
     * The constructor.
     *
     * @param float $azimuth  The azimuth, measured westwards from the South (0, 360)
     * @param float $altitude The altitude, positive above the horizon (-90, 90)
     */
    public function __construct(float $azimuth, float $altitude)
    {
        $this->setMinValue1(0.0);
        $this->setMaxValue1(360.0);
        $this->setMinValue2(-90.0);
        $this->setMaxValue2(90.0);

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
        if ($azimuth < 0.0 || $azimuth > 360.0) {
            $azimuth = $this->bringInInterval1($azimuth);
        }
        $this->_azimuth = $azimuth;
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
        if ($altitude < -90.0 || $altitude > 90.0) {
            $altitude = $this->bringInInterval2($altitude);
        }
        $this->_h = $altitude;
    }

    /**
     * Gets the azimuth.
     *
     * @return float the azimuth, measured westwards from the south
     */
    public function getAzimuth(): float
    {
        return $this->_azimuth;
    }

    /**
     * Gets the altitude.
     *
     * @return float The altitude above the horizon
     */
    public function getAltitude(): float
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
        return $this->convertToDegrees($this->getAzimuth());
    }

    /**
     * Returns a readable string of the altitude above the horizon.
     *
     * @return string A readable string of the altitude above the horizon in degrees,
     *                minutes, seconds
     */
    public function printAltitude(): string
    {
        return $this->convertToDegrees($this->getAltitude());
    }

    /**
     * Converts the local horizontal coordinates to equatorial coordinates.
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
        $phi = $geo_coords->getLatitude();

        // Local hour angle = local siderial time - ra
        $sid = ((
            ($siderial_time->milliseconds / 1000.0) + $siderial_time->second
        ) / 60.0 + $siderial_time->minute) / 60 + $siderial_time->hour;

        $H = rad2deg(
            atan2(
                sin(deg2rad($this->getAzimuth())),
                cos(deg2rad($this->getAzimuth())) * cos(deg2rad($phi))
                + tan(deg2rad($this->getAltitude())) * cos(deg2rad($phi))
            )
        );

        $declination = rad2deg(
            asin(
                sin(deg2rad($phi)) * sin(deg2rad($this->getAltitude()))
                - cos(deg2rad($phi)) * cos(deg2rad($this->getAltitude()))
                * cos(deg2rad($this->getAzimuth()))
            )
        );

        // a = altitude, A = Azimuth
        $x = cos(deg2rad($this->getAltitude())) * sin(deg2rad($this->getAzimuth()));

        $y = (
            sin(deg2rad($phi))
                * cos(deg2rad($this->getAltitude()))
                * cos(
                    deg2rad($this->getAzimuth())
                ) + cos(
                    deg2rad($phi)
                ) * sin(deg2rad($this->getAltitude()))
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
}
