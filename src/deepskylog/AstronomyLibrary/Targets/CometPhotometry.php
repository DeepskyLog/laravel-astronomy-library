<?php

/**
 * The magnitude of a comet.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;

/**
 * The total magnitude of a comet, shared by Elliptic, Parabolic and NearParabolic.
 *
 * The class using the trait provides the heliocentric position of the object
 * and has a $_perihelion_date property.
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
trait CometPhotometry
{
    private ?float $_cometH = null;
    private float $_cometK = 10.0;
    private ?float $_cometPhaseCoeff = null;
    private ?float $_cometKPre = null;
    private ?float $_cometKPost = null;

    /**
     * Sets the photometric parameters of the comet for the total magnitude
     *   m = H + 5 log(delta) + K log(r) + phaseCoeff * alpha.
     *
     * This is the m1 formula of aerith.net and of JPL (M1 and K1). K is the
     * coefficient of log r itself, 2.5 times the activity exponent n that is
     * sometimes quoted instead: K = 10 corresponds to n = 4.
     *
     * @param  float  $H  The absolute total magnitude (M1)
     * @param  float  $K  The coefficient of log r (K1), 10 when it is not known
     * @param  ?float  $phaseCoeff  A linear phase coefficient, in magnitudes per degree
     * @param  ?float  $K_pre  K before the perihelion, for an asymmetric light curve
     * @param  ?float  $K_post  K after the perihelion, for an asymmetric light curve
     */
    public function setCometParams(float $H, float $K = 10.0, ?float $phaseCoeff = null, ?float $K_pre = null, ?float $K_post = null): void
    {
        $this->_cometH = $H;
        $this->_cometK = $K;
        $this->_cometPhaseCoeff = $phaseCoeff;
        $this->_cometKPre = $K_pre;
        $this->_cometKPost = $K_post;
    }

    /**
     * Are the photometric parameters of a comet known?
     */
    public function hasCometParams(): bool
    {
        return $this->_cometH !== null;
    }

    /**
     * The heliocentric rectangular coordinates of the object, in AU, referred
     * to the mean equator and equinox of J2000.
     *
     * @param  Carbon  $date  The date
     * @return array [x, y, z]
     */
    abstract protected function _heliocentricRectangularCoordinates(Carbon $date): array;

    /**
     * The distances that determine the brightness of the object.
     *
     * @param  Carbon  $date  The date
     * @return array [r: distance to the Sun in AU, delta: distance to the Earth in AU, alpha: phase angle in degrees]
     */
    protected function _photometricGeometry(Carbon $date): array
    {
        [$x, $y, $z] = $this->_heliocentricRectangularCoordinates($date);
        $sun = $this->_sunRectangularCoordinates($date);

        // Geocentric position of the Sun plus the heliocentric position of the object
        $X = $sun->getX()->getCoordinate();
        $Y = $sun->getY()->getCoordinate();
        $Z = $sun->getZ()->getCoordinate();

        $r = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $R = sqrt($X ** 2 + $Y ** 2 + $Z ** 2);
        $delta = sqrt(($X + $x) ** 2 + ($Y + $y) ** 2 + ($Z + $z) ** 2);

        // The phase angle Sun - object - Earth follows from the three distances
        $cosAlpha = ($r ** 2 + $delta ** 2 - $R ** 2) / (2 * $r * $delta);
        $alpha = rad2deg(acos(max(-1.0, min(1.0, $cosAlpha))));

        return [$r, $delta, $alpha];
    }

    /**
     * The total magnitude of the comet, see setCometParams().
     *
     * @param  Carbon  $date  The date
     * @return float The magnitude
     */
    protected function _cometMagnitude(Carbon $date): float
    {
        [$r, $delta, $alpha] = $this->_photometricGeometry($date);

        $K = $this->_cometK;
        if ($date < $this->_perihelion_date && $this->_cometKPre !== null) {
            $K = $this->_cometKPre;
        } elseif ($date >= $this->_perihelion_date && $this->_cometKPost !== null) {
            $K = $this->_cometKPost;
        }

        $m = $this->_cometH + 5 * log10($delta) + $K * log10($r);
        if ($this->_cometPhaseCoeff !== null) {
            $m += $this->_cometPhaseCoeff * $alpha;
        }

        return $m;
    }
}
