<?php

/**
 * GalacticCoordinates class.
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
 * GalacticCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class GalacticCoordinates extends Coordinates
{
    private float $_longitude;
    private float $_latitude;

    /**
     * The constructor.
     *
     * @param float $longitude The galactic longitude (0, 360)
     * @param float $latitude  The galactic latitude (-90, 90)
     */
    public function __construct(float $longitude, float $latitude)
    {
        $this->setMinValue1(0.0);
        $this->setMaxValue1(360.0);
        $this->setMinValue2(-90.0);
        $this->setMaxValue2(90.0);

        $this->setLongitude($longitude);
        $this->setLatitude($latitude);
    }

    /**
     * Sets the galactic longitude.
     *
     * @param float $longitude The galactic longitude
     *
     * @return None
     */
    public function setLongitude(float $longitude): void
    {
        if ($longitude < 0.0 || $longitude > 360.0) {
            $longitude = $this->bringInInterval1($longitude);
        }
        $this->_longitude = $longitude;
    }

    /**
     * Sets the galactic latitude.
     *
     * @param float $latitude The galactic latitude
     *
     * @return None
     */
    public function setLatitude(float $latitude): void
    {
        if ($latitude < -90.0 || $latitude > 90.0) {
            $latitude = $this->bringInInterval2($latitude);
        }
        $this->_latitude = $latitude;
    }

    /**
     * Gets the galactic latitude.
     *
     * @return float the galactic latitude in degrees
     */
    public function getLatitude(): float
    {
        return $this->_latitude;
    }

    /**
     * Gets the galactic longitude.
     *
     * @return float The galactic longitude in degrees
     */
    public function getLongitude(): float
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
        return $this->convertToDegrees($this->getLongitude());
    }

    /**
     * Returns a readable string of the galactic latitude.
     *
     * @return string A readable string of the galactic latitude in degrees,
     *                minutes, seconds
     */
    public function printLatitude(): string
    {
        return $this->convertToDegrees($this->getLatitude());
    }

    /**
     * Converts the galactic coordinates to equatorial coordinates.
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function convertToEquatorial(): EquatorialCoordinates
    {
        $b = $this->getLatitude();
        $l = $this->getLongitude();

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
