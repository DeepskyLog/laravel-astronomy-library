<?php

/**
 * Procedures to work with magnitudes.
 *
 * PHP Version 7
 *
 * @category Magnitude
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary;

use InvalidArgumentException;

/**
 * Procedures to work with magnitudes.
 *
 * PHP Version 7
 *
 * @category Magnitude
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Magnitude
{
    /**
     * Calculates the Sqm if the Nelm is given.
     *
     * @param float $nelm      The Naked Eye Limiting Magnitude
     * @param float $fstOffset The offset between the real Nelm and the
     *                         Nelm for the observer
     *
     * @return float The Sqm (Sky Quality Meter) value
     */
    public static function nelmToSqm(float $nelm, float $fstOffset = 0.0): float
    {
        if ($nelm > 8) {
            throw new InvalidArgumentException(
                'No Naked Eye Limiting Magnitude > 8 possible.'
            );
        }
        if ($nelm < 0) {
            throw new InvalidArgumentException(
                'No Naked Eye Limiting Magnitude < 0 possible.'
            );
        }
        $sqm = 21.58
            - 5 * log10(pow(10, (1.586 - ($nelm + $fstOffset) / 5.0)) - 1.0);
        if ($sqm > 22.0) {
            return 22.0;
        } else {
            return $sqm;
        }
    }

    /**
     * Calculates the bortle scale if the Nelm is given.
     *
     * @param float $nelm The Naked Eye Limiting Magnitude
     *
     * @return int The bortle scale value
     */
    public static function nelmToBortle(float $nelm): int
    {
        if ($nelm > 8) {
            throw new InvalidArgumentException(
                'No Naked Eye Limiting Magnitude > 8 possible.'
            );
        }
        if ($nelm < 0) {
            throw new InvalidArgumentException(
                'No Naked Eye Limiting Magnitude < 0 possible.'
            );
        }
        if ($nelm < 3.6) {
            return 9;
        } elseif ($nelm < 3.9) {
            return 8;
        } elseif ($nelm < 4.4) {
            return 7;
        } elseif ($nelm < 4.9) {
            return 6;
        } elseif ($nelm < 5.8) {
            return 5;
        } elseif ($nelm < 6.3) {
            return 4;
        } elseif ($nelm < 6.4) {
            return 3;
        } elseif ($nelm < 6.5) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Calculates the bortle value if the sqm value is given.
     *
     * @param float $sqm The sqm value
     *
     * @return integer The bortle value
     */
    public static function sqmToBortle($sqm): int
    {
        if ($sqm > 22.0) {
            throw new InvalidArgumentException(
                'No SQM value > 22 possible.'
            );
        }
        if ($sqm < 10.0) {
            throw new InvalidArgumentException(
                'No SQM value < 10 possible.'
            );
        }

        if ($sqm <= 17.5) {
            return 9;
        } elseif ($sqm <= 18.0) {
            return 8;
        } elseif ($sqm <= 18.5) {
            return 7;
        } elseif ($sqm <= 19.1) {
            return 6;
        } elseif ($sqm <= 20.4) {
            return 5;
        } elseif ($sqm <= 21.3) {
            return 4;
        } elseif ($sqm <= 21.5) {
            return 3;
        } elseif ($sqm <= 21.7) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Calculates the naked eye limiting magnitude if the sqm value is given.
     *
     * @param float $sqm       The sqm value
     * @param float $fstOffset The offset between the real Nelm and the
     *                         Nelm for the observer
     *
     * @return float The limiting magnitude
     */
    public static function sqmToNelm($sqm, float $fstOffset = 0.0)
    {
        if ($sqm > 22.0) {
            throw new InvalidArgumentException(
                'No SQM value > 22 possible.'
            );
        }
        if ($sqm < 10.0) {
            throw new InvalidArgumentException(
                'No SQM value < 10 possible.'
            );
        }

        $nelm = (7.97 - 5 * log10(1 + pow(10, 4.316 - $sqm / 5.0)));

        if ($nelm < 2.5) {
            $nelm = 2.5;
        }

        return $nelm - $fstOffset;
    }

    /**
     * Calculates the naked eye limiting magnitude if the bortle scale is given.
     *
     * @param int   $bortle    The bortle scale
     * @param float $fstOffset The offset between the real Nelm and the
     *                         Nelm for the observer
     *
     * @return float The naked eye limiting magnitude
     */
    public static function bortleToNelm(int $bortle, float $fstOffset): float
    {
        if ($bortle > 9) {
            throw new InvalidArgumentException(
                'No bortle value > 9 possible.'
            );
        }
        if ($bortle < 1) {
            throw new InvalidArgumentException(
                'No bortle value < 1 possible.'
            );
        }

        if ($bortle == 1) {
            return 6.6 - $fstOffset;
        } elseif ($bortle == 2) {
            return 6.5 - $fstOffset;
        } elseif ($bortle == 3) {
            return 6.4 - $fstOffset;
        } elseif ($bortle == 4) {
            return 6.1 - $fstOffset;
        } elseif ($bortle == 5) {
            return 5.4 - $fstOffset;
        } elseif ($bortle == 6) {
            return 4.7 - $fstOffset;
        } elseif ($bortle == 7) {
            return 4.2 - $fstOffset;
        } elseif ($bortle == 8) {
            return 3.8 - $fstOffset;
        } elseif ($bortle == 9) {
            return 3.6 - $fstOffset;
        } else {
            return '';
        }
    }

    /**
     * Calculates the SQM value if the bortle scale is given.
     *
     * @param int $bortle The bortle scale
     *
     * @return float The SQM value
     */
    public static function bortleToSqm(int $bortle): float
    {
        if ($bortle > 9) {
            throw new InvalidArgumentException(
                'No bortle value > 9 possible.'
            );
        }
        if ($bortle < 1) {
            throw new InvalidArgumentException(
                'No bortle value < 1 possible.'
            );
        }

        if ($bortle == 1) {
            return 21.85;
        } elseif ($bortle == 2) {
            return 21.6;
        } elseif ($bortle == 3) {
            return 21.4;
        } elseif ($bortle == 4) {
            return 20.85;
        } elseif ($bortle == 5) {
            return 19.75;
        } elseif ($bortle == 6) {
            return 18.8;
        } elseif ($bortle == 7) {
            return 18.25;
        } elseif ($bortle == 8) {
            return 17.75;
        } elseif ($bortle == 9) {
            return 17.5;
        } else {
            return '';
        }
    }
}
