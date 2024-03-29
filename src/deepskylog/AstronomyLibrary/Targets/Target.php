<?php

/**
 * The target class describing an astronomical object.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;
use RuntimeException;

/**
 * The target class describing an astronomical object.
 *
 * PHP Version 8
 *
 * @category Target
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */
class Target
{
    // The equatorial coordinates of yesterday
    private EquatorialCoordinates $_equa1;
    // The equatorial coordinates of today
    private EquatorialCoordinates $_equa2;
    // The equatorial coordinates of tomorrow
    private EquatorialCoordinates $_equa3;

    // The height
    private float $_h0 = 0.0;

    // The transit time of this target
    private ?Carbon $_transit = null;
    // The rising time of this target
    private ?Carbon $_rising = null;
    // The setting time of this target
    private ?Carbon $_setting = null;
    // The maximum height during the night
    private ?Coordinate $_maxHeightAtNight = null;
    // The maximum height of the object (during night or day)
    private ?Coordinate $_maxHeight = null;
    // The best time to view the object
    private ?Carbon $_bestTime = null;

    private ?string $_altitudeChart = null;

    // The diameter of the target
    private ?float $_diam1 = null;
    private ?float $_diam2 = null;

    // The magnitude of the object
    private ?float $_magnitude = null;

    // Needed for the calculation of the contrast reserve
    private $_LTCSize = 24;
    private $_angleSize = 7;
    private $_angle = [
        -0.2255, 0.5563, 0.9859, 1.260,
        1.742, 2.083, 2.556,
    ];
    private $_LTC = [
        [
            4, -0.3769, -1.8064, -2.3368, -2.4601,
            -2.5469, -2.5610, -2.5660,
        ],
        [
            5, -0.3315, -1.7747, -2.3337, -2.4608,
            -2.5465, -2.5607, -2.5658,
        ],
        [
            6, -0.2682, -1.7345, -2.3310, -2.4605,
            -2.5467, -2.5608, -2.5658,
        ],
        [
            7, -0.1982, -1.6851, -2.3140, -2.4572,
            -2.5481, -2.5615, -2.5665,
        ],
        [
            8, -0.1238, -1.6252, -2.2791, -2.4462,
            -2.5463, -2.5597, -2.5646,
        ],
        [
            9, -0.0424, -1.5529, -2.2297, -2.4214,
            -2.5343, -2.5501, -2.5552,
        ],
        [
            10, 0.0498, -1.4655, -2.1659, -2.3763,
            -2.5047, -2.5269, -2.5333,
        ],
        [
            11, 0.1596, -1.3581, -2.0810, -2.3036,
            -2.4499, -2.4823, -2.4937,
        ],
        [
            12, 0.2934, -1.2256, -1.9674, -2.1965,
            -2.3631, -2.4092, -2.4318,
        ],
        [
            13, 0.4557, -1.0673, -1.8186, -2.0531,
            -2.2445, -2.3083, -2.3491,
        ],
        [
            14, 0.6500, -0.8841, -1.6292, -1.8741,
            -2.0989, -2.1848, -2.2505,
        ],
        [
            15, 0.8808, -0.6687, -1.3967, -1.6611,
            -1.9284, -2.0411, -2.1375,
        ],
        [
            16, 1.1558, -0.3952, -1.1264, -1.4176,
            -1.7300, -1.8727, -2.0034,
        ],
        [
            17, 1.4822, -0.0419, -0.8243, -1.1475,
            -1.5021, -1.6768, -1.8420,
        ],
        [
            18, 1.8559, 0.3458, -0.4924, -0.8561,
            -1.2661, -1.4721, -1.6624,
        ],
        [
            19, 2.2669, 0.6960, -0.1315, -0.5510,
            -1.0562, -1.2892, -1.4827,
        ],
        [
            20, 2.6760, 1.0880, 0.2060, -0.3210,
            -0.8800, -1.1370, -1.3620,
        ],
        [
            21, 2.7766, 1.2065, 0.3467, -0.1377,
            -0.7361, -0.9964, -1.2439,
        ],
        [
            22, 2.9304, 1.3821, 0.5353, 0.0328,
            -0.5605, -0.8606, -1.1187,
        ],
        [
            23, 3.1634, 1.6107, 0.7708, 0.2531,
            -0.3895, -0.7030, -0.9681,
        ],
        [
            24, 3.4643, 1.9034, 1.0338, 0.4943,
            -0.2033, -0.5259, -0.8288,
        ],
        [
            25, 3.8211, 2.2564, 1.3265, 0.7605,
            0.0172, -0.2992, -0.6394,
        ],
        [
            26, 4.2210, 2.6320, 1.6990, 1.1320,
            0.2860, -0.0510, -0.4080,
        ],
        [
            27, 4.6100, 3.0660, 2.1320, 1.5850,
            0.6520, 0.2410, -0.1210,
        ],
    ];

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(-0.5667);
    }

    /**
     * Set H0.
     *
     * @param  float  $h0  The h0 value
     * @return None
     */
    public function setH0(float $h0): void
    {
        $this->_h0 = $h0;
        $this->_resetGlobalVariables();
    }

    /**
     * Get H0.
     *
     * @return float The h0 value
     */
    public function getH0(): float
    {
        return $this->_h0;
    }

    /**
     * Set the equatorial coordinates of the target.
     *
     * @param  EquatorialCoordinates  $equa  the equatorial coordinates of the object
     * @return None
     */
    public function setEquatorialCoordinates($equa): void
    {
        // The equatorial coordinates of yesterday
        $this->_equa1 = $equa;
        // The equatorial coordinates of today
        $this->_equa2 = $equa;
        // The equatorial coordinates of tomorrow
        $this->_equa3 = $equa;
        $this->_resetGlobalVariables();
    }

    /**
     * Set the equatorial coordinates of the target for yesterday at 0:00 TD.
     *
     * @param  EquatorialCoordinates  $equa  the equatorial coordinates of the object
     *                                       for yesterday
     * @return None
     */
    public function setEquatorialCoordinatesYesterday($equa): void
    {
        // The equatorial coordinates of yesterday
        $this->_equa1 = $equa;
        $this->_resetGlobalVariables();
    }

    /**
     * Set the equatorial coordinates of the target for today at 0:00 TD.
     *
     * @param  EquatorialCoordinates  $equa  the equatorial coordinates of the object
     *                                       for today
     * @return None
     */
    public function setEquatorialCoordinatesToday($equa): void
    {
        // The equatorial coordinates of today
        $this->_equa2 = $equa;
        $this->_resetGlobalVariables();
    }

    /**
     * Set the equatorial coordinates of the target for tomorrow at 0:00 TD.
     *
     * @param  EquatorialCoordinates  $equa  the equatorial coordinates of the object
     *                                       for tomorrow
     * @return None
     */
    public function setEquatorialCoordinatesTomorrow($equa): void
    {
        // The equatorial coordinates of tomorrow
        $this->_equa3 = $equa;
        $this->_resetGlobalVariables();
    }

    /**
     * Reset global variables.
     *
     * @return None
     */
    private function _resetGlobalVariables(): void
    {
        $this->_transit = null;
        $this->_setting = null;
        $this->_rising = null;
        $this->_maxHeight = null;
        $this->_maxHeightAtNight = null;
        $this->_bestTime = null;
    }

    /**
     * Returns the equatorial coordinates of the target from yesterday at 0:00 TD.
     *
     * @return EquatorialCoordinates the equatorial coordinates of yesterday
     */
    public function getEquatorialCoordinatesYesterday(): EquatorialCoordinates
    {
        // The equatorial coordinates of yesterday
        return $this->_equa1;
    }

    /**
     * Returns the equatorial coordinates of the target for today at 0:00 TD.
     *
     * @return EquatorialCoordinates the equatorial coordinates of today
     */
    public function getEquatorialCoordinatesToday(): EquatorialCoordinates
    {
        // The equatorial coordinates of today
        return $this->_equa2;
    }

    /**
     * Returns the equatorial coordinates of the target for today at 0:00 TD.
     *
     * @return EquatorialCoordinates the equatorial coordinates of today
     */
    public function getEquatorialCoordinates(): EquatorialCoordinates
    {
        // The equatorial coordinates of today
        return $this->_equa2;
    }

    /**
     * Returns the equatorial coordinates of the target for tomorrow at 0:00 TD.
     *
     * @return EquatorialCoordinates the equatorial coordinates of tomorrow
     */
    public function getEquatorialCoordinatesTomorrow(): EquatorialCoordinates
    {
        // The equatorial coordinates of tomorrow
        return $this->_equa3;
    }

    /**
     * Get the transit time of this object.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @return Carbon the transit time of the object
     **/
    public function getTransit(): Carbon
    {
        if (! $this->_transit) {
            throw new RuntimeException('First execute the calculateEphemerides method');
        }

        return $this->_transit;
    }

    /**
     * Get the rising time of this object.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @return Carbon The rising time of the object or null if the object does not
     *                set
     **/
    public function getRising(): ?Carbon
    {
        if (! $this->_transit) {
            throw new RuntimeException('First execute the calculateEphemerides method');
        }

        return $this->_rising;
    }

    /**
     * Get the setting time of this object.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @return Carbon The setting time of the object or null if the object does
     *                not set
     **/
    public function getSetting(): ?Carbon
    {
        if (! $this->_transit) {
            throw new RuntimeException('First execute the calculateEphemerides method');
        }

        return $this->_setting;
    }

    /**
     * Get the maximum height of the target during the year.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @return Coordinate the maximum height of the target during the year
     **/
    public function getMaxHeight(): ?Coordinate
    {
        if (! $this->_transit) {
            throw new RuntimeException('First execute the calculateEphemerides method');
        }

        return $this->_maxHeight;
    }

    /**
     * Get the maximum height of the target during the night. If there is
     * no astronomical darkness during the night, the maximum height
     * during the nautical brightness is taken.  If there is also no
     * nautical brightness, null is returned.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @return Coordinate the maximum height of the target during the year
     **/
    public function getMaxHeightAtNight(): ?Coordinate
    {
        if (! $this->_transit) {
            throw new RuntimeException('First execute the calculateEphemerides method');
        }

        return $this->_maxHeightAtNight;
    }

    /**
     * Get the best time to observe this target at the given date.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @return Carbon The best time to observe the target at the given date
     **/
    public function getBestTimeToObserve(): ?Carbon
    {
        if (! $this->_transit) {
            throw new RuntimeException('First execute the calculateEphemerides method');
        }

        return $this->_bestTime;
    }

    /**
     * Calculate rising and the setting of the object.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @param  GeographicalCoordinates  $geo_coords  The geographical
     *                                               coordinates of the observer
     * @param  Carbon  $siderial_time  The apparent siderial time
     *                                 at Greenwich at 0:00 UTC
     * @param  float  $deltaT  Delta t for the given date
     * @return None
     */
    public function calculateEphemerides(
        GeographicalCoordinates $geo_coords,
        Carbon $siderial_time,
        float $deltaT
    ): void {
        $theta0 = (
            (
                (
                    (
                        (
                            $siderial_time->second
                            + $siderial_time->microsecond
                            / 1000000
                        ) / 60
                    )
                    + $siderial_time->minute
                ) / 60
            ) + $siderial_time->hour
        ) * 15.0;

        // Calculate approximate times
        $Hcap0 = rad2deg(
            acos(
                (
                    sin(deg2rad($this->_h0))
                    - sin(deg2rad($geo_coords->getLatitude()->getCoordinate()))
                    * sin(
                        deg2rad(
                            $this->getEquatorialCoordinatesToday()
                                ->getDeclination()->getCoordinate()
                        )
                    )
                )
                / (cos(deg2rad($geo_coords->getLatitude()->getCoordinate()))
                * cos(
                    deg2rad(
                        $this->getEquatorialCoordinatesToday()
                            ->getDeclination()->getCoordinate()
                    )
                ))
            )
        );

        $m0 = ($this->getEquatorialCoordinatesToday()->getRA()->getCoordinate()
            * 15.0
            - $geo_coords->getLongitude()->getCoordinate() - $theta0)
            / 360.0;
        $m0 -= floor($m0);

        $theta = $this->_calculateTheta($theta0, $m0);

        // Calculate the height at transit
        $H = $this->_getLocalHourAngle(
            $theta,
            $geo_coords->getLongitude()->getCoordinate(),
            $this->getEquatorialCoordinatesToday()->getRA()->getCoordinate() * 15.0
        );

        $transitHeight = $this->_getHeight(
            $geo_coords->getLatitude()->getCoordinate(),
            $this->getEquatorialCoordinatesToday()->getDeclination()
                ->getCoordinate(),
            $H
        );

        if (is_nan($Hcap0)) {
            $m1 = 99;
            $m2 = 99;
        } else {
            $m1 = $m0 - $Hcap0 / 360.0;
            $m1 -= floor($m1);
            $m2 = $m0 + $Hcap0 / 360.0;
            $m2 -= floor($m2);
        }

        if ($this->getEquatorialCoordinatesYesterday()->getRA()->getCoordinate() == $this->getEquatorialCoordinatesTomorrow()->getRA()->getCoordinate()
            && $this->getEquatorialCoordinatesYesterday()->getDeclination()->getCoordinate() == $this->getEquatorialCoordinatesTomorrow()->getDeclination()->getCoordinate()
        ) {
            $a = 0.0;
            $b = 0.0;
            $adec = 0.0;
            $bdec = 0.0;
            // Target does not move.
            $targetDoesNotMove = true;
        } else {
            // Extra calculation for moving targets.
            // We use delta t for the given date.
            $targetDoesNotMove = false;
            $a = $this->getEquatorialCoordinatesToday()->getRA()->getCoordinate()
                - $this->getEquatorialCoordinatesYesterday()->getRA()
                ->getCoordinate();
            $b = $this->getEquatorialCoordinatesTomorrow()->getRA()->getCoordinate()
                - $this->getEquatorialCoordinatesToday()->getRA()->getCoordinate();

            $adec = $this->getEquatorialCoordinatesToday()->getDeclination()
                ->getCoordinate()
                - $this->getEquatorialCoordinatesYesterday()->getDeclination()
                ->getCoordinate();
            $bdec = $this->getEquatorialCoordinatesTomorrow()->getDeclination()
                ->getCoordinate()
                - $this->getEquatorialCoordinatesToday()->getDeclination()
                ->getCoordinate();

            [$transitHeight, $H, $deltaInterpol] = $this->_calculateHeight(
                $theta0,
                $m0,
                $deltaT,
                $targetDoesNotMove,
                $a,
                $b,
                $adec,
                $bdec,
                $geo_coords
            );

            $deltaM = -$H / 360.0;

            $m0 = $deltaM + $m0;

            // ******
            // Rising
            // ******
            if ($m1 != 99) {
                [$height, $H, $deltaInterpol] = $this->_calculateHeight(
                    $theta0,
                    $m1,
                    $deltaT,
                    $targetDoesNotMove,
                    $a,
                    $b,
                    $adec,
                    $bdec,
                    $geo_coords
                );

                $m1 = $this->_getDeltaM(
                    $height,
                    $this->getH0(),
                    $deltaInterpol,
                    $geo_coords->getLatitude()->getCoordinate(),
                    $H
                ) + $m1;
            }

            // *******
            // Setting
            // *******
            if ($m1 != 99) {
                [$height, $H, $deltaInterpol] = $this->_calculateHeight(
                    $theta0,
                    $m2,
                    $deltaT,
                    $targetDoesNotMove,
                    $a,
                    $b,
                    $adec,
                    $bdec,
                    $geo_coords
                );

                $m2 = $this->_getDeltaM(
                    $height,
                    $this->getH0(),
                    $deltaInterpol,
                    $geo_coords->getLatitude()->getCoordinate(),
                    $H
                ) + $m2;
            }
        }

        $transit = $m0 * 24.0;
        $this->_transit = $this->_createTime($transit, $siderial_time);
        $this->_bestTime = $this->_transit;

        if ($m1 == 99) {
            $this->_rising = Carbon::make(null);
        } else {
            $rising = $m1 * 24.0;
            $this->_rising = $this->_createTime($rising, $siderial_time);
        }

        if ($m2 == 99) {
            $this->_setting = Carbon::make(null);
        } else {
            $setting = $m2 * 24.0;
            $this->_setting = $this->_createTime($setting, $siderial_time);
        }

        // Also calculate the altitude during the transit
        $sun_info = date_sun_info(
            $siderial_time->timestamp,
            $geo_coords->getLatitude()->getCoordinate(),
            $geo_coords->getLongitude()->getCoordinate()
        );

        $during_night = true;

        if ($sun_info['astronomical_twilight_begin'] === true) {
            $endOfNight = Carbon::createFromTimestamp(
                $sun_info['nautical_twilight_begin']
            );

            $startOfNight = Carbon::createFromTimestamp(
                $sun_info['nautical_twilight_end']
            );
            if ($sun_info['nautical_twilight_begin'] === true) {
                $this->_maxHeight = new Coordinate($transitHeight, -90.0, 90.0);
                $this->_maxHeightAtNight = null;
                $this->_bestTime = null;
            }
        } else {
            $endOfNight = Carbon::createFromTimestamp(
                $sun_info['astronomical_twilight_begin']
            );

            $startOfNight = Carbon::createFromTimestamp(
                $sun_info['astronomical_twilight_end']
            );
        }

        // Check if the transit is before the beginning of the night
        if ($this->_transit->isBefore(
            Carbon::createFromTimestamp(
                $sun_info['astronomical_twilight_end']
            )->toDate()
        )
        ) {
            // Check if the transit is after the end of the night
            if ($this->_transit->isAfter($endOfNight->toDate())) {
                $during_night = false;
            }
        }

        if (! $during_night) {
            $th = new Coordinate($transitHeight, -90.0, 90.0);

            // Calculate the height at the end of the night
            $astroend = $endOfNight->hour + $endOfNight->minute / 60.0;

            $height = $this->_calculateHeight(
                $theta0,
                $astroend / 24.0,
                $deltaT,
                $targetDoesNotMove,
                $a,
                $b,
                $adec,
                $bdec,
                $geo_coords
            )[0];

            // Calculate the height at the beginning of the night
            $astrobegin = $startOfNight->hour + $startOfNight->minute / 60.0;

            $height2 = $this->_calculateHeight(
                $theta0,
                $astrobegin / 24.0,
                $deltaT,
                $targetDoesNotMove,
                $a,
                $b,
                $adec,
                $bdec,
                $geo_coords
            )[0];

            // Compare and use the hightest height as the best height for the target
            if ($height2 > $height) {
                $th = new Coordinate($height2, -90.0, 90.0);
                $this->_bestTime = $startOfNight;
            } else {
                $th = new Coordinate($height, -90.0, 90.0);
                $this->_bestTime = $endOfNight;
            }
            // If max height < 0.0 at astronomical darkness, try nautical darkness.
            if ($th->getCoordinate() < 0.0) {
                if ($endOfNight != Carbon::createFromTimestamp(
                    $sun_info['nautical_twilight_begin']
                )
                ) {
                    $endOfNight = Carbon::createFromTimestamp(
                        $sun_info['nautical_twilight_begin']
                    );
                    $startOfNight = Carbon::createFromTimestamp(
                        $sun_info['nautical_twilight_end']
                    );
                    $astroend = $endOfNight->hour + $endOfNight->minute / 60.0;

                    $height = $this->_calculateHeight(
                        $theta0,
                        $astroend / 24.0,
                        $deltaT,
                        $targetDoesNotMove,
                        $a,
                        $b,
                        $adec,
                        $bdec,
                        $geo_coords
                    )[0];

                    // Calculate the height at the beginning of the night
                    $astrobegin = $startOfNight->hour + $startOfNight->minute / 60.0;

                    $height2 = $this->_calculateHeight(
                        $theta0,
                        $astrobegin / 24.0,
                        $deltaT,
                        $targetDoesNotMove,
                        $a,
                        $b,
                        $adec,
                        $bdec,
                        $geo_coords
                    )[0];

                    // Compare and use the hightest height as the best height
                    // for the target
                    if ($height2 > $height) {
                        $th = new Coordinate($height2, -90.0, 90.0);
                        $this->_bestTime = $startOfNight;
                    } else {
                        $th = new Coordinate($height, -90.0, 90.0);
                        $this->_bestTime = $endOfNight;
                    }
                }
            }
        } else {
            $th = new Coordinate($transitHeight, -90.0, 90.0);
        }
        $this->_maxHeight = new Coordinate($transitHeight, -90.0, 90.0);
        $this->_maxHeightAtNight = $th;
    }

    /**
     * Calculates the height of the object at a given moment.
     * Chapter 15 of Astronomical Algorithms.
     *
     * @param  float  $theta0  Theta0 of the target
     * @param  float  $time  The time to calculate the
     *                       height for, in decimal hours
     * @param  float  $deltaT  Delta t for the given date
     * @param  bool  $targetDoesNotMove  True if the target does not
     *                                   move
     * @param  float  $a  The RA from today - the RA
     *                    from yesterday
     * @param  float  $b  The RA from tomorrow - the
     *                    RA from yesterday
     * @param  float  $adec  The declination from today -
     *                       the declination of yesterday
     * @param  float  $bdec  The declination from
     *                       tomorrow - the declination
     *                       of today
     * @param  GeographicalCoordinates  $geo_coords  The geographical coordinates
     *                                               to calculate the height from
     * @return array the height of the object, $H
     */
    private function _calculateHeight(
        $theta0,
        $time,
        $deltaT,
        $targetDoesNotMove,
        $a,
        $b,
        $adec,
        $bdec,
        $geo_coords
    ): array {
        $theta = $this->_calculateTheta($theta0, $time);
        $n = $this->_calculateN($time, $deltaT);

        if (! $targetDoesNotMove) {
            $alphaInterpol = $this->_interpolate(
                $this->getEquatorialCoordinatesToday()->getRA()->getCoordinate(),
                $n,
                $a,
                $b
            ) * 15.0;
            $deltaInterpol = $this->_interpolate(
                $this->getEquatorialCoordinatesToday()->getDeclination()
                    ->getCoordinate(),
                $n,
                $adec,
                $bdec
            );
        } else {
            $alphaInterpol = $this->getEquatorialCoordinatesToday()
                ->getRA()->getCoordinate()
                * 15.0;
            $deltaInterpol = $this->getEquatorialCoordinatesToday()
                ->getDeclination()->getCoordinate();
        }

        $H = $this->_getLocalHourAngle(
            $theta,
            $geo_coords->getLongitude()->getCoordinate(),
            $alphaInterpol
        );

        $height = $this->_getHeight(
            $geo_coords->getLatitude()->getCoordinate(),
            $deltaInterpol,
            $H
        );

        return [$height, $H, $deltaInterpol];
    }

    /**
     * Calculate theta.
     *
     * @param  float  $theta0  Theta0
     * @param  float  $m  m0 for the transit, m1 for the rising, m2 for the setting
     * @return float Theta
     */
    private function _calculateTheta(float $theta0, float $m): float
    {
        $theta = $theta0 + 360.985647 * $m;
        $theta /= 360.0;
        $theta -= floor($theta);
        $theta *= 360.0;

        return $theta;
    }

    /**
     * Calculate n.
     *
     * @param  float  $m  The m value
     * @param  float  $deltaT  Delta T for the given date
     * @return float the value for n
     */
    private function _calculateN(float $m, float $deltaT): float
    {
        return $m + $deltaT / 86400;
    }

    /**
     * Interpolate to find the coordinates of the given time.
     *
     * @param  float  $coord  the coordinate for today
     * @param  float  $n  the n value
     * @param  float  $a  the movement in coordinates from yesterday to today
     * @param  float  $b  the movement in coordinates from today to tomorrow
     * @return float the interpolated value
     */
    private function _interpolate(
        float $coord,
        float $n,
        float $a,
        float $b
    ): float {
        $c = $b - $a;

        return $coord + $n / 2.0 * ($a + $b + $n * $c);
    }

    /**
     * Get the local hour angle of the object.
     *
     * @param  float  $theta  The theta value
     * @param  float  $longitude  The longitude of the location
     * @param  float  $alphaInterpol  Description
     * @return float
     */
    private function _getLocalHourAngle(
        float $theta,
        float $longitude,
        float $alphaInterpol
    ): float {
        return $theta + $longitude - $alphaInterpol;
    }

    /**
     * Calculate the height of the object.
     *
     * @param  float  $latitude  The latitude of the location
     * @param  float  $deltaInterpol  The interpolation in declination for the object
     * @param  float  $H  The hour angle
     * @return float the height of the object
     */
    private function _getHeight(
        float $latitude,
        float $deltaInterpol,
        float $H
    ): float {
        return rad2deg(
            asin(
                sin(deg2rad($latitude))
                * sin(deg2rad($deltaInterpol))
                + cos(deg2rad($latitude))
                * cos(deg2rad($deltaInterpol))
                * cos(deg2rad($H))
            )
        );
    }

    /**
     * Calculate the correction for m.
     *
     * @param  float  $height  The height of the object
     * @param  float  $h0  The h0 value
     * @param  float  $deltaInterpol  The interpolation in declination for the object
     * @param  float  $latitude  The latitude of the location
     * @param  float  $H  The hour angle
     * @return float the correction for m
     */
    private function _getDeltaM(
        float $height,
        float $h0,
        float $deltaInterpol,
        float $latitude,
        float $H
    ): float {
        return ($height - $h0)
            / (360.0 * cos(deg2rad($deltaInterpol))
            * cos(deg2rad($latitude))
            * sin(deg2rad($H)));
    }

    /**
     * Create a Carbon time from an integer.
     *
     * @param  float  $time  the time as integer
     * @param  Carbon  $carbonTime  carbon time, only used for the date
     * @return Carbon The Carbon Time
     */
    private function _createTime(float $time, Carbon $carbonTime): Carbon
    {
        // Same for rising, transit and setting
        $hour = intval($time);
        $minute = intval(($time - $hour) * 60.0);
        $second = intval(((($time - $hour) * 60.0) - $minute) * 60.0);

        return $carbonTime->copy()->hour($hour)->minute($minute)
            ->second($second)->microsecond(0);
    }

    /**
     * Creates a chart with the altitude of the target.
     *
     * @param  GeographicalCoordinates  $geo_coords  The geographical
     *                                               coordinates of the observer
     * @param  Carbon  $date  the date for which to make the
     *                        chart
     * @return string The altitude chart
     */
    public function altitudeGraph(
        GeographicalCoordinates $geo_coords,
        Carbon $date
    ): string {
        if (! $this->_altitudeChart) {
            $image = imagecreatetruecolor(1000, 400);

            // Show the night
            $sun_info = date_sun_info(
                $date->timestamp,
                $geo_coords->getLatitude()->getCoordinate(),
                $geo_coords->getLongitude()->getCoordinate()
            );
            $sun_info2 = date_sun_info(
                $date->addDay()->timestamp,
                $geo_coords->getLatitude()->getCoordinate(),
                $geo_coords->getLongitude()->getCoordinate()
            );

            // Check there is a nautical twilight
            if ($sun_info2['nautical_twilight_begin']) {
                $endOfNauticalNight = Carbon::createFromTimestamp(
                    $sun_info2['nautical_twilight_begin']
                )->timezone($date->timezone);

                $endNautical = (
                    $endOfNauticalNight->second / 60 + $endOfNauticalNight->minute
                ) / 60 + $endOfNauticalNight->hour - 12;

                if ($endNautical < 0) {
                    $endNautical += 24;
                }
            } else {
                $endNautical = 12;
            }

            if ($sun_info['nautical_twilight_end']) {
                $startOfNauticalNight = Carbon::createFromTimestamp(
                    $sun_info['nautical_twilight_end']
                )->timezone($date->timezone);

                $startNautical = (
                    $startOfNauticalNight->second / 60 + $startOfNauticalNight->minute
                ) / 60 + $startOfNauticalNight->hour - 12;

                if ($startNautical < 0) {
                    $startNautical += 24;
                }
            } else {
                $startNautical = 12;
            }

            // Check if there is a real night
            if ($sun_info2['astronomical_twilight_begin']) {
                $endOfNight = Carbon::createFromTimestamp(
                    $sun_info2['astronomical_twilight_begin']
                )->timezone($date->timezone);

                $endAstronomical = (
                    $endOfNight->second / 60 + $endOfNight->minute
                ) / 60 + $endOfNight->hour - 12;

                if ($endAstronomical < 0) {
                    $endAstronomical += 24;
                }
            } else {
                $endAstronomical = 12;
            }

            if ($sun_info['astronomical_twilight_end']) {
                $startOfNight = Carbon::createFromTimestamp(
                    $sun_info['astronomical_twilight_end']
                )->timezone($date->timezone);

                $startAstronomical = (
                    $startOfNight->second / 60 + $startOfNight->minute
                ) / 60 + $startOfNight->hour - 12;

                if ($startAstronomical < 0) {
                    $startAstronomical += 24;
                }
            } else {
                $startAstronomical = 12;
            }

            $daycolor = imagecolorallocate($image, 0, 0, 200);
            $twilightcolor = imagecolorallocate($image, 100, 100, 200);

            imagefilledrectangle($image, 70, 5, 70 + $startNautical * 37, 365, $daycolor);
            imagefilledrectangle($image, 70 + $endNautical * 37, 5, 958, 365, $daycolor);

            imagefilledrectangle($image, 70 + $startNautical * 37, 5, 70 + $startAstronomical * 37, 365, $twilightcolor);
            imagefilledrectangle($image, 70 + $endAstronomical * 37, 5, 70 + $endNautical * 37, 365, $twilightcolor);

            // Start at noon
            $date->hour = 12;

            $textcolor = imagecolorallocate($image, 255, 255, 255);
            $axiscolor = imagecolorallocate($image, 150, 150, 150);

            for ($i = 0; $i <= 24; $i++) {
                // Calculate the apparent siderial time
                $siderial_time = Time::apparentSiderialTime($date, $geo_coords);

                imagestring($image, 2, 55 + $i * 37, 370, $date->isoFormat('HH:mm'), $textcolor);

                // Use the correct coordinates for moving targets
                if ($this->getEquatorialCoordinatesToday()->getRA() == $this->getEquatorialCoordinatesYesterday()->getRA()
                    && $this->getEquatorialCoordinatesToday()->getDeclination() == $this->getEquatorialCoordinatesYesterday()->getDeclination()
                ) {
                    $coords = $this->getEquatorialCoordinates();
                } else {
                    // Coordinates are for 0:00 TD
                    $raToday = $this->getEquatorialCoordinatesToday()->getRA()->getCoordinate();
                    $declToday = $this->getEquatorialCoordinatesToday()
                        ->getDeclination()->getCoordinate();
                    $raTomorrow = $this->getEquatorialCoordinatesTomorrow()->getRA()->getCoordinate();
                    $declTomorrow = $this->getEquatorialCoordinatesTomorrow()
                        ->getDeclination()->getCoordinate();

                    $raDiff = $raTomorrow - $raToday;
                    if (abs($raDiff) > 12) {
                        if ($raToday > $raTomorrow) {
                            $raDiff = 24 + $raDiff;
                        } else {
                            $raDiff = $raDiff - 24;
                        }
                    }
                    $raInterval = $raDiff / 24;
                    $ra = $raToday + $raInterval * (12 + $i);
                    $decl = $declToday
                        + ($declTomorrow - $declToday) / 24 * (12 + $i);

                    $coords = new EquatorialCoordinates($ra, $decl);
                }
                // Calculate the horizontal coordinates
                $horizontal = $coords->convertToHorizontal(
                    $geo_coords,
                    $siderial_time
                );

                // Add an hour
                $date->addHour();

                imagefilledellipse($image, 70 + $i * 37, 185 - $horizontal->getAltitude()->getCoordinate() * 2, 5, 5, $textcolor);
                imageline($image, 70 + $i * 37, 365, 70 + $i * 37, 355, $axiscolor);
            }

            imagestring($image, 2, 35, 360, '-90', $textcolor);
            imageline($image, 70, 365, 958, 365, $axiscolor);
            imagestring($image, 2, 35, 300, '-60', $textcolor);
            imageline($image, 70, 305, 958, 305, $axiscolor);
            imagestring($image, 2, 35, 240, '-30', $textcolor);
            imageline($image, 70, 245, 958, 245, $axiscolor);
            imagestring($image, 2, 35, 180, '0', $textcolor);
            imageline($image, 70, 185, 958, 185, $axiscolor);
            imagestring($image, 2, 35, 120, '30', $textcolor);
            imageline($image, 70, 125, 958, 125, $axiscolor);
            imagestring($image, 2, 35, 60, '60', $textcolor);
            imageline($image, 70, 65, 958, 65, $axiscolor);
            imagestring($image, 2, 35, 0, '90', $textcolor);
            imageline($image, 70, 5, 958, 5, $axiscolor);

            // Begin capturing the byte stream
            ob_start();

            // generate the byte stream
            imagepng($image);

            // and finally retrieve the byte stream
            $rawImageBytes = ob_get_clean();

            $this->_altitudeChart = "<img src='data:image/jpeg;base64,"
                .base64_encode($rawImageBytes)."' />";
        }

        return $this->_altitudeChart;
    }

    /**
     * Returns the constellation from the given coordinates.
     *
     * @return string The constellation (3-character code in Latin for example: ERI, LEO, LMI, ...)
     */
    public function getConstellation(): string
    {
        return $this->getEquatorialCoordinates()->getConstellation();
    }

    /**
     * Calculates the eccentric Anomaly using the equation of Kepler.
     *
     * @param  float  $eccentricity  The eccentricity of the orbit
     * @param  float  $meanAnomaly  The mean anomaly of the orbit in degrees
     * @param  float  $accuracy  The accuracy of the result
     * @return float The eccentric anomaly
     *
     * See chapter 30 of Astronomical Algorithms
     */
    public function eccentricAnomaly(float $eccentricity, float $meanAnomaly, float $accuracy): float
    {
        $e = $eccentricity * 180.0 / pi();

        $old = $meanAnomaly;
        $new = $meanAnomaly + $e * sin(deg2rad($old));

        while (abs($old - $new) > $accuracy) {
            $old = $new;
            $new = $meanAnomaly + $e * sin(deg2rad($old));
        }

        return $new;
    }

    /**
     * Set the diameter of the target.
     *
     * @param  float  $diam1  The diam1 in arcseconds
     * @param  float  $diam2  The diam2 in arcseconds
     * @return None
     */
    public function setDiameter(?float $diam1, ?float $diam2 = 0.0): void
    {
        if ($diam2 == 0) {
            $diam2 = $diam1;
        }
        $this->_diam1 = $diam1;
        $this->_diam2 = $diam2;
    }

    /**
     * Get diameter.
     *
     * @return array The diam1 and diam2 value
     */
    public function getDiameter(): array
    {
        return [$this->_diam1, $this->_diam2];
    }

    /**
     * Set the magnitude of the target.
     *
     * @param  float  $magnitude  The magnitude
     * @return None
     */
    public function setMagnitude(?float $magnitude): void
    {
        $this->_magnitude = $magnitude;
    }

    /**
     * Get the magnitude of the target.
     *
     * @return float The magnitude
     */
    public function getMagnitude(): ?float
    {
        return $this->_magnitude;
    }

    /**
     * Calculates the SBObj of the target.  This is needed to calculate the contrast of the target.
     *
     * @return ?float THe SBObj of the target
     */
    public function calculateSBObj(): ?float
    {
        if ($this->_magnitude && ($this->_magnitude != 99.9) && (($this->_diam1 != 0) || ($this->_diam2 != 0))) {
            $SBObj = ($this->_magnitude + (2.5 * log10(2827.0 * ($this->_diam1 / 60) * ($this->_diam2 / 60))));
        } else {
            $SBObj = null;
        }

        return $SBObj;
    }

    /**
     * Calculates the contrast reserve of the target.
     * If the contrast difference is < 0, the object is not visible.
     *    contrast difference < -0.2 : Not visible
     *     -0.2 < contrast diff < 0.1 : questionable
     *     0.10 < contrast diff < 0.35 : Difficult
     *     0.35 < contrast diff < 0.5 : Quite difficult to see
     *     0.50 < contr diff < 1.0 : Easy to see
     *     1.00 < contrast diff : Very easy to see.
     *
     * @param  float  $SBObj  SBObj as calculated.
     * @param  float  $sqm  The value from the Sky Quality Meter describing the sky darkness.
     * @param  float  $diameter  The diameter of the used instrument (in mm)
     * @param  float  $magnification  The used magnification
     */
    public function calculateContrastReserve(float $SBObj, ?float $sqm, ?float $diameter, ?float $magnification): ?float
    {
        if (! $sqm) {
            return null;
        }
        if (! $diameter) {
            return null;
        }

        $aperIn = $diameter / 25.4;

        // Minimum useful magnification
        $SBB1 = $sqm - (5 * log10(2.833 * $aperIn));

        $minObjArcmin = $this->_diam1 / 60.0;
        $maxObjArcmin = $this->_diam2 / 60.0;

        if ($minObjArcmin > $maxObjArcmin) {
            $temp = $minObjArcmin;
            $minObjArcmin = $maxObjArcmin;
            $maxObjArcmin = $temp;
        }
        $maxLog = 37;

        // Log Object contrast
        $logObjContrast = -0.4 * ($SBObj - $sqm);

        // The preparations are finished, we can now start the calculations
        $x = $magnification;

        $SBReduc = 5 * log10($x);
        $SBB = $SBB1 + $SBReduc;

        /* 2 dimensional interpolation of LTC array */
        $ang = $x * $minObjArcmin;
        $logAng = log10($ang);
        $SB = $SBB;
        $I = 0;

        /* int of surface brightness */
        $intSB = (int) $SB;
        /* surface brightness index A */
        $SBIA = $intSB - 4;
        /* min index must be at least 0 */
        if ($SBIA < 0) {
            $SBIA = 0;
        }
        /* max SBIA index cannot > 22 so that max SBIB <= 23 */
        if ($SBIA > $this->_LTCSize - 2) {
            $SBIA = $this->_LTCSize - 2;
        }
        /* surface brightness index B */
        $SBIB = $SBIA + 1;

        while ($I < $this->_angleSize && $logAng > $this->_angle[$I++]);

        /* found 1st Angle[] value > LogAng, so back up 2 */
        $I -= 2;
        if ($I < 0) {
            $I = 0;
            $logAng = $this->_angle[0];
        }

        /* ie, if LogAng = 4 and Angle[I] = 3 and Angle[I+1] = 5,
        InterpAngle = .5, or .5 of the way between Angle[I] and Angle{I+1] */
        $interpAngle = ($logAng - $this->_angle[$I])
                    / ($this->_angle[$I + 1] - $this->_angle[$I]);
        /* add 1 to I because first entry in LTC is
        sky background brightness */
        $interpA = $this->_LTC[$SBIA][$I + 1]
                    + $interpAngle
                    * ($this->_LTC[$SBIA][$I + 2]
                    - $this->_LTC[$SBIA][$I + 1]);
        $interpB = $this->_LTC[$SBIB][$I + 1]
                    + $interpAngle
                    * ($this->_LTC[$SBIB][$I + 2]
                    - $this->_LTC[$SBIB][$I + 1]);
        if ($SB < $this->_LTC[0][0]) {
            $SB = $this->_LTC[0][0];
        }
        if ($intSB >= $this->_LTC[$this->_LTCSize - 1][0]) {
            $logThreshContrast = $interpB
                        + ($SB - $this->_LTC[$this->_LTCSize - 1][0])
                        * ($interpB - $interpA);
        } else {
            $logThreshContrast = $interpA + ($SB - $intSB)
                        * ($interpB - $interpA);
        }

        if ($logThreshContrast > $maxLog) {
            $logThreshContrast = $maxLog;
        } else {
            if ($logThreshContrast < -$maxLog) {
                $logThreshContrast = -$maxLog;
            }
        }

        $logContrastDiff = $logObjContrast - $logThreshContrast;

        return $logContrastDiff;
    }

    /**
     * Calculates the detection magnification of the target.
     *
     * @param  float  $SBObj  SBObj as calculated.
     * @param  float  $sqm  The value from the Sky Quality Meter describing the sky darkness.
     * @param  float  $diameter  The diameter of the used instrument (in mm)
     * @param  array  $magnifications  An array with the possible magnifications
     */
    public function calculateBestMagnification(float $SBObj, ?float $sqm, ?float $diameter, array $magnifications): ?float
    {
        $bestContrast = -999;
        $bestMagnification = 0;
        foreach ($magnifications as $magnification) {
            $contrast = $this->calculateContrastReserve($SBObj, $sqm, $diameter, $magnification);
            if ($contrast > $bestContrast) {
                $bestContrast = $contrast;
                $bestMagnification = $magnification;
            }
        }

        return $bestMagnification;
    }
}
