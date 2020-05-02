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

use InvalidArgumentException;

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
            throw new InvalidArgumentException(
                'Galactic longitude should be between 0° and 360°.'
            );
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
            throw new InvalidArgumentException(
                'Galactic latitude should be between -90° and 90°.'
            );
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
}
