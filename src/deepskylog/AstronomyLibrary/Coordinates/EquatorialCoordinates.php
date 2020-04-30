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

use InvalidArgumentException;

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
            throw new InvalidArgumentException(
                'Right Ascension should be between 0 and 24 hours.'
            );
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
            throw new InvalidArgumentException(
                'Declination should be between -90° and 90°.'
            );
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
}
