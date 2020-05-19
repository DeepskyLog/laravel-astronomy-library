<?php

/**
 * The target class describing the moon.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

/**
 * The target class describing the moon.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Moon extends Target
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(
            0.7275 * $this->calculateHorizontalMoonParallax() - 0.5666667
        );
    }

    /**
     * Calculates the horizontal moon parallax.
     *
     * To implement from chapter 29.
     *
     * @return float the horizontal moon parallax
     */
    public function calculateHorizontalMoonParallax(): float
    {
        return 0.950744559450172;
    }
}
