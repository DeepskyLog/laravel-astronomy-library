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
    private array $_cometLightCurve = [];

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
     * Sets a light curve in parts, each valid for a range of days around the
     * perihelion, as aerith.net publishes them:
     *
     *   m1 = 7.0 + 5 log d + 10.0 log r          [-85,  0]
     *   m1 = 4.3 + 5 log d + 11.0 log r(t + 10)  [  0, 52]
     *
     * The second line uses the distance to the Sun of 10 days later. For a
     * date outside all ranges the nearest part is used. A light curve takes
     * precedence over setCometParams().
     *
     * @param  array  $segments  Each ['from' => ?float, 'to' => ?float, 'H' => float,
     *                           'K' => float, 'shift' => float]: days from the
     *                           perihelion (null for an open end), the absolute
     *                           magnitude, the coefficient of log r and the
     *                           shift in days of r
     */
    public function setCometLightCurve(array $segments): void
    {
        $this->_cometLightCurve = [];
        foreach ($segments as $segment) {
            if (! isset($segment['H'], $segment['K']) || ! is_numeric($segment['H']) || ! is_numeric($segment['K'])) {
                continue;
            }
            $this->_cometLightCurve[] = [
                'from' => is_numeric($segment['from'] ?? null) ? (float) $segment['from'] : null,
                'to' => is_numeric($segment['to'] ?? null) ? (float) $segment['to'] : null,
                'H' => (float) $segment['H'],
                'K' => (float) $segment['K'],
                'shift' => is_numeric($segment['shift'] ?? null) ? (float) $segment['shift'] : 0.0,
            ];
        }
    }

    /**
     * Are the photometric parameters of a comet known?
     */
    public function hasCometParams(): bool
    {
        return $this->_cometH !== null || ! empty($this->_cometLightCurve);
    }

    /**
     * The part of the light curve for a number of days from the perihelion:
     * the part whose range contains it, else the part with the nearest range.
     */
    protected function _lightCurveSegment(float $days): array
    {
        $best = null;
        $bestDistance = INF;
        foreach ($this->_cometLightCurve as $segment) {
            $from = $segment['from'] ?? -INF;
            $to = $segment['to'] ?? INF;
            $distance = $days < $from ? $from - $days : ($days > $to ? $days - $to : 0.0);
            // On a shared boundary, the later part starts
            if ($distance < $bestDistance || ($distance == 0.0 && $bestDistance == 0.0)) {
                $best = $segment;
                $bestDistance = $distance;
            }
        }

        return $best;
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

        if (! empty($this->_cometLightCurve)) {
            $days = $this->_perihelion_date->diffInSeconds($date, false) / 86400.0;
            $segment = $this->_lightCurveSegment($days);
            if ($segment['shift'] != 0.0) {
                [$r] = $this->_photometricGeometry($date->copy()->addSeconds((int) round($segment['shift'] * 86400)));
            }

            return $segment['H'] + 5 * log10($delta) + $segment['K'] * log10($r);
        }

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
