<?php

/**
 * RectangularCoordinates class.
 *
 * PHP Version 8
 *
 * @category Coordinates
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Coordinates;

/**
 * RectangularCoordinates class.
 *
 * PHP Version 8
 *
 * @category Coordinates
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */
class RectangularCoordinates
{
    private Coordinate $_X;
    private Coordinate $_Y;
    private Coordinate $_Z;

    /**
     * The constructor.
     *
     * @param float $X The rectangular coordinate X (-180, 180)
     * @param float $Y The rectangular coordinate Y (-180, 180)
     * @param float $Z The rectangular coordinate Z (-180, 180)
     */
    public function __construct(float $X, float $Y, float $Z)
    {
        $this->setX($X);
        $this->setY($Y);
        $this->setZ($Z);
    }

    /**
     * Sets the rectangular coordinate X.
     *
     * @param float $X The rectangular coordinate X
     *
     * @return None
     */
    public function setX(float $X): void
    {
        $this->_X = new Coordinate($X, -180.0, 180.0);
    }

    /**
     * Sets the rectangular coordinate Y.
     *
     * @param float $Y The rectangular coordinate Y
     *
     * @return None
     */
    public function setY(float $Y): void
    {
        $this->_Y = new Coordinate($Y, -180.0, 180.0);
    }

    /**
     * Sets the rectangular coordinate Z.
     *
     * @param float $Y The rectangular coordinate Z
     *
     * @return None
     */
    public function setZ(float $Z): void
    {
        $this->_Z = new Coordinate($Z, -180.0, 180.0);
    }

    /**
     * Gets the rectangular coordinate X.
     *
     * @return Coordinate The rectangular coordinate X
     */
    public function getX(): Coordinate
    {
        return $this->_X;
    }

    /**
     * Gets the rectangular coordinate Y.
     *
     * @return Coordainte the rectangular coordinate Y
     */
    public function getY(): Coordinate
    {
        return $this->_Y;
    }

    /**
     * Gets the rectangular coordinate Z.
     *
     * @return Coordainte the rectangular coordinate Z
     */
    public function getZ(): Coordinate
    {
        return $this->_Z;
    }
}
