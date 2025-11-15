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
        -0.2255,
        0.5563,
        0.9859,
        1.260,
        1.742,
        2.083,
        2.556,
    ];
    private $_LTC = [
        [
            4,
            -0.3769,
            -1.8064,
            -2.3368,
            -2.4601,
            -2.5469,
            -2.5610,
            -2.5660,
        ],
        [
            5,
            -0.3315,
            -1.7747,
            -2.3337,
            -2.4608,
            -2.5465,
            -2.5607,
            -2.5658,
        ],
        [
            6,
            -0.2682,
            -1.7345,
            -2.3310,
            -2.4605,
            -2.5467,
            -2.5608,
            -2.5658,
        ],
        [
            7,
            -0.1982,
            -1.6851,
            -2.3140,
            -2.4572,
            -2.5481,
            -2.5615,
            -2.5665,
        ],
        [
            8,
            -0.1238,
            -1.6252,
            -2.2791,
            -2.4462,
            -2.5463,
            -2.5597,
            -2.5646,
        ],
        [
            9,
            -0.0424,
            -1.5529,
            -2.2297,
            -2.4214,
            -2.5343,
            -2.5501,
            -2.5552,
        ],
        [
            10,
            0.0498,
            -1.4655,
            -2.1659,
            -2.3763,
            -2.5047,
            -2.5269,
            -2.5333,
        ],
        [
            11,
            0.1596,
            -1.3581,
            -2.0810,
            -2.3036,
            -2.4499,
            -2.4823,
            -2.4937,
        ],
        [
            12,
            0.2934,
            -1.2256,
            -1.9674,
            -2.1965,
            -2.3631,
            -2.4092,
            -2.4318,
        ],
        [
            13,
            0.4557,
            -1.0673,
            -1.8186,
            -2.0531,
            -2.2445,
            -2.3083,
            -2.3491,
        ],
        [
            14,
            0.6500,
            -0.8841,
            -1.6292,
            -1.8741,
            -2.0989,
            -2.1848,
            -2.2505,
        ],
        [
            15,
            0.8808,
            -0.6687,
            -1.3967,
            -1.6611,
            -1.9284,
            -2.0411,
            -2.1375,
        ],
        [
            16,
            1.1558,
            -0.3952,
            -1.1264,
            -1.4176,
            -1.7300,
            -1.8727,
            -2.0034,
        ],
        [
            17,
            1.4822,
            -0.0419,
            -0.8243,
            -1.1475,
            -1.5021,
            -1.6768,
            -1.8420,
        ],
        [
            18,
            1.8559,
            0.3458,
            -0.4924,
            -0.8561,
            -1.2661,
            -1.4721,
            -1.6624,
        ],
        [
            19,
            2.2669,
            0.6960,
            -0.1315,
            -0.5510,
            -1.0562,
            -1.2892,
            -1.4827,
        ],
        [
            20,
            2.6760,
            1.0880,
            0.2060,
            -0.3210,
            -0.8800,
            -1.1370,
            -1.3620,
        ],
        [
            21,
            2.7766,
            1.2065,
            0.3467,
            -0.1377,
            -0.7361,
            -0.9964,
            -1.2439,
        ],
        [
            22,
            2.9304,
            1.3821,
            0.5353,
            0.0328,
            -0.5605,
            -0.8606,
            -1.1187,
        ],
        [
            23,
            3.1634,
            1.6107,
            0.7708,
            0.2531,
            -0.3895,
            -0.7030,
            -0.9681,
        ],
        [
            24,
            3.4643,
            1.9034,
            1.0338,
            0.4943,
            -0.2033,
            -0.5259,
            -0.8288,
        ],
        [
            25,
            3.8211,
            2.2564,
            1.3265,
            0.7605,
            0.0172,
            -0.2992,
            -0.6394,
        ],
        [
            26,
            4.2210,
            2.6320,
            1.6990,
            1.1320,
            0.2860,
            -0.0510,
            -0.4080,
        ],
        [
            27,
            4.6100,
            3.0660,
            2.1320,
            1.5850,
            0.6520,
            0.2410,
            -0.1210,
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

        if (
            $this->getEquatorialCoordinatesYesterday()->getRA()->getCoordinate() == $this->getEquatorialCoordinatesTomorrow()->getRA()->getCoordinate()
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
        // Also need sun info for the following day to get the full night bounds
        $sun_info2 = date_sun_info(
            $siderial_time->copy()->addDay()->timestamp,
            $geo_coords->getLatitude()->getCoordinate(),
            $geo_coords->getLongitude()->getCoordinate()
        );

        $during_night = true;

        // Prefer astronomical twilight bounds when present, otherwise fall back
        // to nautical twilight if available. If neither exists, treat as no
        // usable night for nightly max calculations.
        $hasAstronomical = (bool) ($sun_info['astronomical_twilight_end'] && $sun_info2['astronomical_twilight_begin']);
        $hasNautical = (bool) ($sun_info['nautical_twilight_end'] && $sun_info2['nautical_twilight_begin']);

        // Defensive check: some PHP builds or locales may return epoch-ish
        // timestamps (year 1970) or other sentinel values for missing
        // twilight entries. If the constructed Carbon falls in year 1970,
        // treat that twilight as missing so we don't use bogus bounds.
        if ($hasAstronomical) {
            $tmpStart = Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($siderial_time->timezone);
            $tmpEnd = Carbon::createFromTimestamp($sun_info2['astronomical_twilight_begin'])->timezone($siderial_time->timezone);
            if ($tmpStart->year === 1970 || $tmpEnd->year === 1970) {
                $hasAstronomical = false;
            }
        }
        if ($hasNautical) {
            $tmpStartN = Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])->timezone($siderial_time->timezone);
            $tmpEndN = Carbon::createFromTimestamp($sun_info2['nautical_twilight_begin'])->timezone($siderial_time->timezone);
            if ($tmpStartN->year === 1970 || $tmpEndN->year === 1970) {
                $hasNautical = false;
            }
        }

        if ($hasAstronomical) {
            $startOfNight = Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($siderial_time->timezone);
            $endOfNight = Carbon::createFromTimestamp($sun_info2['astronomical_twilight_begin'])->timezone($siderial_time->timezone);
        } elseif ($hasNautical) {
            // No astronomical darkness -> use nautical twilight bounds as requested
            $startOfNight = Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])->timezone($siderial_time->timezone);
            $endOfNight = Carbon::createFromTimestamp($sun_info2['nautical_twilight_begin'])->timezone($siderial_time->timezone);
        } else {
            // No useful twilight bounds; mark that there is no night
            $startOfNight = Carbon::createFromTimestamp(0)->timezone($siderial_time->timezone);
            $endOfNight = Carbon::createFromTimestamp(0)->timezone($siderial_time->timezone);
        }

        // Determine whether the transit occurs during the night interval.
        // Night interval may wrap past midnight, so handle both cases.
        // The computed $this->_transit is anchored to the date used as the
        // siderial-time base; that may put the transit on the previous or
        // next calendar day relative to the night bounds. Try the transit
        // on the day before, the day of, and the day after and pick the
        // candidate that falls inside the night interval.
        $during_night = false;
        $transitInNight = null;
        $candidates = [
            $this->_transit->copy(),
            $this->_transit->copy()->subDay(),
            $this->_transit->copy()->addDay(),
        ];
        foreach ($candidates as $cand) {
            if ($startOfNight->lte($endOfNight)) {
                if ($cand->betweenIncluded($startOfNight, $endOfNight)) {
                    $during_night = true;
                    $transitInNight = $cand;
                    break;
                }
            } else {
                if ($cand->gte($startOfNight) || $cand->lte($endOfNight)) {
                    $during_night = true;
                    $transitInNight = $cand;
                    break;
                }
            }
        }
        // If we found a transit candidate that occurs during the night,
        // prefer that as the best time to observe.
        if ($transitInNight !== null) {
            $this->_bestTime = $transitInNight;
        }

        if (! $during_night) {
            // When the transit does not occur during the chosen night
            // interval, prefer the true maximum DURING the night when an
            // astronomical night exists. Only when no astronomical night
            // is present (but a nautical one may exist) do we fall back to
            // comparing twilight endpoint altitudes.
            if ($hasAstronomical) {
                // Sample the night at 15 minute intervals to find the true
                // maximum altitude during the night interval. This ensures
                // we pick the nightly maximum even when the transit lies
                // outside the night bounds.
                $bestNightAlt = -INF;
                $bestNightTime = null;
                $sampleTime = $startOfNight->copy();
                while ($sampleTime->lte($endOfNight)) {
                    $timeHours = $sampleTime->hour + $sampleTime->minute / 60.0 + $sampleTime->second / 3600.0;
                    $mForCalc = $timeHours / 24.0;
                    [$hval] = $this->_calculateHeight(
                        $theta0,
                        $mForCalc,
                        $deltaT,
                        $targetDoesNotMove,
                        $a,
                        $b,
                        $adec,
                        $bdec,
                        $geo_coords
                    );
                    if (is_finite($hval) && $hval > $bestNightAlt) {
                        $bestNightAlt = $hval;
                        $bestNightTime = $sampleTime->copy();
                    }
                    $sampleTime->addMinutes(15);
                }

                // Use the nightly maximum (even if below horizon). This
                // obeys the rule to choose the maximum during the real
                // night rather than a twilight endpoint.
                if ($bestNightTime !== null) {
                    $th = new Coordinate($bestNightAlt, -90.0, 90.0);
                    $this->_bestTime = $bestNightTime;
                } else {
                    // Fallback: no samples? fall back to transitHeight
                    $th = new Coordinate($transitHeight, -90.0, 90.0);
                }
            } else {
                // No astronomical night: fall back to the previous behavior
                // of comparing twilight endpoint altitudes (nautical case).
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

                // Compare and use the highest height as the best height for the target
                if ($height2 > $height) {
                    $th = new Coordinate($height2, -90.0, 90.0);
                    $this->_bestTime = $startOfNight;
                } else {
                    $th = new Coordinate($height, -90.0, 90.0);
                    $this->_bestTime = $endOfNight;
                }

                // If selected twilight-based maximum is negative and nautical
                // twilight exists we may try nautical bounds (preserve old
                // behavior). This block mirrors the previous fallback.
                if ($th->getCoordinate() < 0.0 && $hasNautical) {
                    $endOfNight = Carbon::createFromTimestamp($sun_info2['nautical_twilight_begin'])
                        ->timezone($siderial_time->timezone);
                    $startOfNight = Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])
                        ->timezone($siderial_time->timezone);
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
        if (! isset($hasAstronomical) && ! isset($hasNautical)) {
            // safety: if bounds weren't computed, set to null
            $this->_maxHeightAtNight = null;
        } elseif (! $hasAstronomical && ! $hasNautical) {
            // no astronomical nor nautical darkness -> no nightly max
            $this->_maxHeightAtNight = null;
        } else {
            $this->_maxHeightAtNight = $th;
        }
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

            // Always draw the 'day' regions (left of nautical start and right of nautical end)
            imagefilledrectangle($image, 70, 5, 70 + $startNautical * 37, 365, $daycolor);
            imagefilledrectangle($image, 70 + $endNautical * 37, 5, 958, 365, $daycolor);

            // Only draw the astronomical-twilight shaded regions when we really
            // have valid astronomical twilight bounds. Some PHP builds/locales
            // return sentinel/epoch timestamps (year 1970) or nulls; guard against
            // that so we don't draw a small twilight band when astronomical
            // twilight is absent for the date (e.g. high-latitude summer).
            $hasAstronomical = (bool) ($sun_info['astronomical_twilight_end'] && $sun_info2['astronomical_twilight_begin']);
            if ($hasAstronomical) {
                $tmpStart = Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($date->timezone);
                $tmpEnd = Carbon::createFromTimestamp($sun_info2['astronomical_twilight_begin'])->timezone($date->timezone);
                if ($tmpStart->year === 1970 || $tmpEnd->year === 1970) {
                    $hasAstronomical = false;
                }
            }

            if ($hasAstronomical) {
                imagefilledrectangle($image, 70 + $startNautical * 37, 5, 70 + $startAstronomical * 37, 365, $twilightcolor);
                imagefilledrectangle($image, 70 + $endAstronomical * 37, 5, 70 + $endNautical * 37, 365, $twilightcolor);
            } else {
                // No astronomical twilight for this date: fill the entire night
                // area (between nautical start and nautical end) with light blue so
                // the background isn't black. Guard against invalid numeric
                // defaults (e.g. both equal 12) which would produce an empty area.
                $xStart = 70 + $startNautical * 37;
                $xEnd = 70 + $endNautical * 37;
                if ($xEnd > $xStart) {
                    imagefilledrectangle($image, (int) $xStart, 5, (int) $xEnd, 365, $twilightcolor);
                }
            }

            // Start at noon
            $date->hour = 12;
            $date->minute = 0;

            $textcolor = imagecolorallocate($image, 255, 255, 255);
            $axiscolor = imagecolorallocate($image, 150, 150, 150);

            // We'll keep track of the previous plotted positive point
            $prevX = null;
            $prevY = null;

            // Prepare moon plotting: dashed white line
            $moon = new Moon();
            $prevMoonX = null;
            $prevMoonY = null;
            // Define a longer dash style: more white pixels then a larger gap
            // (repeat white pixels 6 times, gap as axiscolor 10 times for more space)
            $moonDashStyle = [
                $textcolor,
                $textcolor,
                $textcolor,
                $textcolor,
                $textcolor,
                $textcolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
                $axiscolor,
            ];
            imagesetstyle($image, $moonDashStyle);
            $moonStyle = IMG_COLOR_STYLED;

            // Increase sampling: steps per hour (4 -> 15min sampling).
            $stepsPerHour = 4;
            $stepMinutes = 60 / $stepsPerHour; // 15
            $totalSteps = 24 * $stepsPerHour; // 96

            for ($i = 0; $i <= $totalSteps; $i++) {
                // Use a copy of the loop date as the absolute time of this sample
                $stepDate = $date->copy();

                // Calculate the apparent siderial time for this sample
                $siderial_time = Time::apparentSiderialTime($stepDate, $geo_coords);

                // Draw time labels only at whole hours (every $stepsPerHour step)
                if ($i % $stepsPerHour == 0) {
                    $labelX = 55 + ($i / $stepsPerHour) * 37;
                    imagestring($image, 2, (int) $labelX, 370, $stepDate->isoFormat('HH:mm'), $textcolor);
                }

                // Recalculate equatorial coordinates for this exact sample time when possible.
                // Prefer subclass implementations if available.
                if (method_exists($this, 'calculateApparentEquatorialCoordinates')) {
                    // This method sets equatorial coordinates for today/tomorrow/yesterday
                    $this->calculateApparentEquatorialCoordinates($stepDate->copy());
                    $coords = $this->getEquatorialCoordinatesToday();
                } elseif (method_exists($this, 'calculateEquatorialCoordinates')) {
                    // Topocentric equatorial coordinates (needs geo coords and height)
                    // Use height 0.0 by default
                    $this->calculateEquatorialCoordinates($stepDate->copy(), $geo_coords, 0.0);
                    $coords = $this->getEquatorialCoordinatesToday();
                } else {
                    // Fallback: interpolate between stored today/tomorrow values (original behavior)
                    if (
                        $this->getEquatorialCoordinatesToday()->getRA() == $this->getEquatorialCoordinatesYesterday()->getRA()
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
                }

                // Calculate the horizontal coordinates for this sample
                $horizontal = $coords->convertToHorizontal(
                    $geo_coords,
                    $siderial_time
                );

                // Advance the loop date by one step for the next iteration
                $date->addMinutes($stepMinutes);

                // Only plot non-negative altitudes (0..90 deg)
                $alt = $horizontal->getAltitude()->getCoordinate();
                if ($alt >= 0.0) {
                    // Map 0..90 deg to pixel range 365..5 (365 at 0deg, 5 at 90deg)
                    $y = 365 - $alt * 4;
                    if ($y < 5) {
                        $y = 5;
                    }
                    if ($y > 365) {
                        $y = 365;
                    }
                    $x = 70 + ($i / $stepsPerHour) * 37;

                    // If there's a previous positive point, draw a line to it
                    if ($prevX !== null && $prevY !== null) {
                        imageline($image, $prevX, (int) $prevY, $x, (int) $y, $textcolor);
                    }

                    // Draw the marker for this point
                    // Draw a small marker only at whole hours to reduce clutter
                    // if ($i % $stepsPerHour == 0) {
                    //     imagefilledellipse($image, (int) $x, (int) $y, 5, 5, $textcolor);
                    // }

                    // Save as previous
                    $prevX = $x;
                    $prevY = $y;
                } else {
                    // Reset previous when there's a gap (negative altitude)
                    $prevX = null;
                    $prevY = null;
                }
                // ----- Moon plotting: compute moon altitude at this same time -----
                // Use a copy of the date so Moon methods that mutate the passed Carbon
                // don't affect the plotting loop's $date.
                $moonDate = $date->copy();
                // Calculate moon apparent equatorial coords for this moment
                $moon->calculateApparentEquatorialCoordinates($moonDate);
                $moonCoords = $moon->getEquatorialCoordinatesToday();
                $moonHorizontal = $moonCoords->convertToHorizontal($geo_coords, $siderial_time);

                $moonAlt = $moonHorizontal->getAltitude()->getCoordinate();
                if ($moonAlt >= 0.0) {
                    $moonY = 365 - $moonAlt * 4;
                    if ($moonY < 5) {
                        $moonY = 5;
                    }
                    if ($moonY > 365) {
                        $moonY = 365;
                    }
                    $moonX = 70 + ($i / $stepsPerHour) * 37;

                    if ($prevMoonX !== null && $prevMoonY !== null) {
                        // Draw dashed moon line
                        imageline($image, (int) $prevMoonX, (int) $prevMoonY, (int) $moonX, (int) $moonY, $moonStyle);
                    }

                    $prevMoonX = $moonX;
                    $prevMoonY = $moonY;
                } else {
                    $prevMoonX = null;
                    $prevMoonY = null;
                }
                // Draw minor tick only at whole hours
                if ($i % $stepsPerHour == 0) {
                    $tickX = 70 + ($i / $stepsPerHour) * 37;
                    imageline($image, (int) $tickX, 365, (int) $tickX, 355, $axiscolor);
                }
            }
            // Draw a small legend for the moon (top-right)
            // Move slightly left so it doesn't touch the image border
            $legendX = 830;
            $legendY = 15;
            // Draw a short dashed sample line using the same style
            $sampleLen = 60;
            // We draw the sample in the legend area: from legendX to legendX+sampleLen
            // Use the styled color index
            imagesetstyle($image, $moonDashStyle);
            imageline($image, $legendX, $legendY + 6, $legendX + $sampleLen, $legendY + 6, IMG_COLOR_STYLED);
            // Label it 'Moon'
            imagestring($image, 2, $legendX + $sampleLen + 8, $legendY - 2, 'Moon', $textcolor);
            // Show only positive elevation axis (0..90)
            imagestring($image, 2, 35, 360, '0' . chr(176), $textcolor);
            imageline($image, 70, 365, 958, 365, $axiscolor);
            imagestring($image, 2, 35, 240, '30' . chr(176), $textcolor);
            imageline($image, 70, 245, 958, 245, $axiscolor);
            imagestring($image, 2, 35, 120, '60' . chr(176), $textcolor);
            imageline($image, 70, 125, 958, 125, $axiscolor);
            imagestring($image, 2, 35, 0, '90' . chr(176), $textcolor);
            imageline($image, 70, 5, 958, 5, $axiscolor);

            // Begin capturing the byte stream
            ob_start();

            // generate the byte stream
            imagepng($image);

            // and finally retrieve the byte stream
            $rawImageBytes = ob_get_clean();

            $this->_altitudeChart = "<img src='data:image/jpeg;base64,"
                . base64_encode($rawImageBytes) . "' />";
        }

        return $this->_altitudeChart;
    }

    /**
     * Creates a yearly chart with the maximum altitude per month.
     * For each month we sample every 10 days (day 1, 11 and 21) and
     * compute the maximum altitude during the night. The X axis shows
     * months, the Y axis the maximum altitude during the night (0..90).
     * If a month's sampled nights all have no astronomical twilight,
     * that month's background is filled blue; otherwise kept black.
     * The graph uses a single connected line and no separate point markers.
     *
     * @param  GeographicalCoordinates  $geo_coords  The geographical coordinates
     * @param  Carbon  $date  A representative date (year used)
     * @return string The generated chart as an embedded image
     */
    public function yearGraph(GeographicalCoordinates $geo_coords, Carbon $date, bool $debug = false): string
    {
        $image = imagecreatetruecolor(1000, 400);

        // Background black
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 1000, 400, $black);

        $textcolor = imagecolorallocate($image, 255, 255, 255);
        $axiscolor = imagecolorallocate($image, 150, 150, 150);
        // More saturated / darker blue fill and a brighter cyan border to make
        // blue month regions stand out clearly.
        $blue = imagecolorallocate($image, 0, 38, 153); // darker saturated blue
        $blueBorder = imagecolorallocate($image, 0, 160, 255); // thin border color

        // plotting area left..right
        $left = 70;
        $right = 958;
        $width = $right - $left;

        // Compute X positions for month STARTS (first of each month).
        // We map the day-of-year of the first day of each month to the
        // plotting width. This places month labels and ticks at the
        // first-of-month instead of the middle of the month.
        $year = $date->year;
        $yearStart = Carbon::create($year, 1, 1, 12, 0, 0, $date->timezone);
        $daysInYear = $yearStart->isLeapYear() ? 366 : 365;

        $xs = [];
        for ($m = 0; $m < 12; $m++) {
            $firstOfMonth = Carbon::create($year, $m + 1, 1, 12, 0, 0, $date->timezone);
            $doy = $firstOfMonth->dayOfYear; // 1..daysInYear
            $frac = ($doy - 1) / $daysInYear; // 0-based fraction across year
            $xs[$m] = $left + $frac * $width;
        }

        // For plotting, put the monthly maxima exactly at the first-of-month
        // X positions so the line starts at Jan 1 and the December point is
        // at Dec 1; we'll draw an extra segment from Dec 1 to the right
        // edge so the line visibly continues through to the year's end.
        $plotXs = $xs;

        $monthMaxes = [];
        $monthAllNoAstronomical = [];
        $monthNoNautCounts = [];
        // Collect per-sample datapoints for plotting (ensure at least 5 per month)
        $monthSamples = [];

        // Steps for sampling the night: every 10 minutes (6 steps/hour)
        $stepsPerHour = 6;
        $stepMinutes = 60 / $stepsPerHour; // 10

        // How many samples per month to take (increase for higher resolution).
        // Higher values increase accuracy but also CPU cost. Ten samples is a
        // reasonable default (roughly every 2-3 days).
        $samplesPerMonth = 10;

        for ($m = 1; $m <= 12; $m++) {
            // Generate evenly spaced sample days across the month up to the
            // 28th day to avoid month-length variability and keep sampling
            // uniform across months. This produces approx $samplesPerMonth
            // samples per month.
            $daysInSpan = 28; // sample within days 1..28
            $step = max(1, floor($daysInSpan / $samplesPerMonth));
            $sampleDays = [];
            for ($d = 1; $d <= $daysInSpan; $d += $step) {
                $sampleDays[] = $d;
            }
            $values = [];
            $monthSamples[$m - 1] = [];
            $noAstrCount = 0;
            foreach ($sampleDays as $d) {
                // create a Carbon for the sample date at noon so date_sun_info covers the following night
                $sample = Carbon::create($year, $m, $d, 12, 0, 0, $date->timezone);

                // get sun info for sample day and next day
                $sun_info = date_sun_info(
                    $sample->timestamp,
                    $geo_coords->getLatitude()->getCoordinate(),
                    $geo_coords->getLongitude()->getCoordinate()
                );
                $sun_info2 = date_sun_info(
                    $sample->copy()->addDay()->timestamp,
                    $geo_coords->getLatitude()->getCoordinate(),
                    $geo_coords->getLongitude()->getCoordinate()
                );

                // Determine astronomical night bounds (primary) and fall back to
                // nautical if astronomical is not present. The blue-month marker
                // should reflect absence of astronomical darkness per user request.
                $hasAstronomical = (bool) ($sun_info['astronomical_twilight_end'] && $sun_info2['astronomical_twilight_begin']);

                // Track whether we've already counted this sample as 'no astronomical'
                $countedNoAstr = false;
                if (! $hasAstronomical) {
                    $noAstrCount++;
                    $countedNoAstr = true;
                }

                // Choose which twilight bounds we will use for sampling: prefer
                // astronomical, otherwise use nautical when available.
                if ($sun_info['astronomical_twilight_end'] && $sun_info2['astronomical_twilight_begin']) {
                    $startOfNight = Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($sample->timezone);
                    $endOfNight = Carbon::createFromTimestamp($sun_info2['astronomical_twilight_begin'])->timezone($sample->timezone);
                } elseif ($sun_info['nautical_twilight_end'] && $sun_info2['nautical_twilight_begin']) {
                    $startOfNight = Carbon::createFromTimestamp($sun_info['nautical_twilight_end'])->timezone($sample->timezone);
                    $endOfNight = Carbon::createFromTimestamp($sun_info2['nautical_twilight_begin'])->timezone($sample->timezone);
                } else {
                    // no valid twilight bounds; record a 0.0 sample for this day
                    // so that we still have datapoints to plot and don't
                    // short-circuit the month's sampling.
                    $val = 0.0;
                    $values[] = $val;
                    $monthSamples[$m - 1][] = ['day' => $d, 'val' => $val];
                    // Proceed to next sample day
                    continue;
                }

                // (debug log removed)

                // If the returned start is the Unix epoch (1970) or start equals end,
                // treat this as a missing astronomical twilight and count it towards
                // making the month blue (per user request). Also skip sampling for
                // this night since the bounds are not usable.
                if (! $countedNoAstr && ($startOfNight->year === 1970 || $startOfNight->eq($endOfNight))) {
                    // We saw an epoch/sentinel or degenerate twilight interval.
                    // Count this as a 'no astronomical' sample but DO NOT skip
                    // the ephemeris calculation — the internal
                    // calculateEphemerides() method is more robust and may
                    // still produce a usable nightly maximum (see user's note
                    // about June). Previously we short-circuited and pushed a
                    // 0.0 which caused months like June to be zeroed even when
                    // a valid max exists.
                    $noAstrCount++;
                    // (debug log removed)
                    // don't continue; fall through to ephemeris calculation
                }

                // Normalize times to an interval starting at local noon - 12h mapping like altitudeGraph
                $start = ($startOfNight->hour + $startOfNight->minute / 60.0 + $startOfNight->second / 3600.0) - 12.0;
                if ($start < 0) {
                    $start += 24.0;
                }
                $end = ($endOfNight->hour + $endOfNight->minute / 60.0 + $endOfNight->second / 3600.0) - 12.0;
                if ($end < 0) {
                    $end += 24.0;
                }

                // If end < start it means the night wraps past midnight in the mapped 0..24 space
                if ($end <= $start) {
                    $end += 24.0;
                }

                // Instead of sampling every few minutes, use the established
                // ephemeris calculation which determines the maximum height
                // during the night and stores it in getMaxHeightAtNight().
                // This centralizes the logic and avoids duplicating horizon/
                // twilight handling here.
                $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($sample);
                $deltaTVal = Time::deltaT($sample);

                try {
                    // calculateEphemerides will populate _maxHeightAtNight
                    $this->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaTVal);
                    $coord = $this->getMaxHeightAtNight();
                } catch (\Exception $e) {
                    // In case of unexpected failure, treat as no visible height
                    $coord = null;
                }

                if ($coord !== null && is_finite($coord->getCoordinate())) {
                    $val = $coord->getCoordinate();
                    if ($val >= 0.0) {
                        $values[] = min(90.0, $val);
                        $monthSamples[$m - 1][] = ['day' => $d, 'val' => min(90.0, $val)];
                    } else {
                        $values[] = 0.0;
                        $monthSamples[$m - 1][] = ['day' => $d, 'val' => 0.0];
                    }
                } else {
                    $values[] = 0.0;
                    $monthSamples[$m - 1][] = ['day' => $d, 'val' => 0.0];
                }
            }

            // decide month's maximum: the maximum of sampled values (if any)
            if (! empty($values)) {
                $monthMaxes[$m - 1] = max($values);
            } else {
                $monthMaxes[$m - 1] = 0.0;
            }
            // Record count and decide month-blue flag (astronomical-based).
            $monthNoAstrCounts[$m - 1] = $noAstrCount;
            // Mark month as having 'no astronomical night' only if ALL sampled
            // nights lack astronomical twilight (matching the header description
            // "If a month's sampled nights all have no astronomical twilight,
            // that month's background is filled blue").
            $monthAllNoAstronomical[$m - 1] = ($noAstrCount === count($sampleDays));

            // (debug log removed)
        }

        // Fill background blue where there is no astronomical darkness.
        // Instead of coarse month-wide flags we compute a day-resolution
        // mask across the year so partial-month spans (e.g. ending
        // July 17) are represented accurately.

        $noAstrByDay = array_fill(1, $daysInYear, false);
        for ($day = 1; $day <= $daysInYear; $day++) {
            $sample = $yearStart->copy()->addDays($day - 1);

            $sun_info = date_sun_info(
                $sample->timestamp,
                $geo_coords->getLatitude()->getCoordinate(),
                $geo_coords->getLongitude()->getCoordinate()
            );
            $sun_info2 = date_sun_info(
                $sample->copy()->addDay()->timestamp,
                $geo_coords->getLatitude()->getCoordinate(),
                $geo_coords->getLongitude()->getCoordinate()
            );

            $hasAstronomical = (bool) ($sun_info['astronomical_twilight_end'] && $sun_info2['astronomical_twilight_begin']);
            if ($hasAstronomical) {
                // guard against sentinel/epoch timestamps
                $tmpStart = Carbon::createFromTimestamp($sun_info['astronomical_twilight_end'])->timezone($sample->timezone);
                $tmpEnd = Carbon::createFromTimestamp($sun_info2['astronomical_twilight_begin'])->timezone($sample->timezone);
                if ($tmpStart->year === 1970 || $tmpEnd->year === 1970) {
                    $hasAstronomical = false;
                }
            }

            $noAstrByDay[$day] = ! $hasAstronomical;
        }

        // Find contiguous runs of no-astronomical days and draw them. We map
        // day-of-year to an X coordinate across the plotting width to allow
        // sub-month precision.
        $inRun = false;
        $runStart = 0;
        for ($d = 1; $d <= $daysInYear; $d++) {
            $isNoAstr = $noAstrByDay[$d];
            if ($isNoAstr && ! $inRun) {
                $inRun = true;
                $runStart = $d;
            } elseif (! $isNoAstr && $inRun) {
                $runEnd = $d - 1;
                $startFrac = ($runStart - 1) / $daysInYear;
                $endFrac = $runEnd / $daysInYear;
                $x1 = (int) floor($left + $startFrac * $width) - 1;
                $x2 = (int) ceil($left + $endFrac * $width) + 1;
                if ($x1 < $left) {
                    $x1 = $left;
                }
                if ($x2 > $right) {
                    $x2 = $right;
                }
                imagefilledrectangle($image, $x1, 5, $x2, 365, $blue);
                imagerectangle($image, $x1, 5, $x2, 365, $blueBorder);
                $inRun = false;
            }
        }
        // If we ended while still in a run, close it now
        if ($inRun) {
            $runEnd = $daysInYear;
            $startFrac = ($runStart - 1) / $daysInYear;
            $endFrac = $runEnd / $daysInYear;
            $x1 = (int) floor($left + $startFrac * $width) - 1;
            $x2 = (int) ceil($left + $endFrac * $width) + 1;
            if ($x1 < $left) {
                $x1 = $left;
            }
            if ($x2 > $right) {
                $x2 = $right;
            }
            imagefilledrectangle($image, $x1, 5, $x2, 365, $blue);
            imagerectangle($image, $x1, 5, $x2, 365, $blueBorder);
        }

        // Draw month labels and horizontal axis
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        for ($m = 0; $m < 12; $m++) {
            $labelX = (int) $xs[$m] - 10;
            imagestring($image, 2, $labelX, 370, $monthNames[$m], $textcolor);
            imageline($image, (int) $xs[$m], 365, (int) $xs[$m], 355, $axiscolor);
            // We keep textual debug in the error log, but do not draw any
            // debug overlay on the generated image so the output is clean.
        }

        // Add a tick and label for January of the next year at the right edge.
        // We intentionally do NOT compute or plot a projected Jan value; the
        // graph should show the December sample as the last data point but
        // include a visual tick for Jan 1 of the following year.
        $labelXNextJan = (int) $right - 10;
        imagestring($image, 2, $labelXNextJan, 370, 'Jan', $textcolor);
        imageline($image, (int) $right, 365, (int) $right, 355, $axiscolor);

        // Draw Y axis markers (0,30,60,90)
        imagestring($image, 2, 35, 360, '0' . chr(176), $textcolor);
        imageline($image, $left, 365, $right, 365, $axiscolor);
        imagestring($image, 2, 35, 240, '30' . chr(176), $textcolor);
        imageline($image, $left, 245, $right, 245, $axiscolor);
        imagestring($image, 2, 35, 120, '60' . chr(176), $textcolor);
        imageline($image, $left, 125, $right, 125, $axiscolor);
        imagestring($image, 2, 35, 0, '90' . chr(176), $textcolor);
        imageline($image, $left, 5, $right, 5, $axiscolor);

        // Draw a connected white line through all chronological samples
        // collected across the year. This produces multiple datapoints per
        // month (at least the configured sample count, and at least 5 when
        // requested) and gives a more detailed curve than a single monthly
        // maximum.
        $allSamples = [];
        for ($m = 1; $m <= 12; $m++) {
            if (! isset($monthSamples[$m - 1])) {
                continue;
            }
            foreach ($monthSamples[$m - 1] as $s) {
                $sampleDate = Carbon::create($year, $m, $s['day'], 12, 0, 0, $date->timezone);
                $doy = $sampleDate->dayOfYear;
                $frac = ($doy - 1) / $daysInYear;
                $x = $left + $frac * $width;
                $alt = $s['val'];
                $y = 365 - $alt * 4;
                if ($y < 5) {
                    $y = 5;
                }
                if ($y > 365) {
                    $y = 365;
                }
                $allSamples[] = ['doy' => $doy, 'x' => (int) $x, 'y' => (int) $y, 'val' => $alt];
            }
        }

        // They are already chronological by month/sample order, but sort to be safe
        usort($allSamples, function ($a, $b) {
            return $a['doy'] <=> $b['doy'];
        });

        $prevX = null;
        $prevY = null;
        foreach ($allSamples as $pt) {
            // treat non-positive values as gaps
            if ($pt['val'] <= 0.0) {
                $prevX = null;
                $prevY = null;
                continue;
            }
            $x = $pt['x'];
            $y = $pt['y'];
            if ($prevX !== null && $prevY !== null) {
                imageline($image, (int) $prevX, (int) $prevY, (int) $x, (int) $y, $textcolor);
            }
            $prevX = $x;
            $prevY = $y;
        }

        // Extend the final segment past the Dec 1 tick to the right edge
        // so the plotted line visually reaches the Jan tick. Use the last
        // plotted y-value for a horizontal continuation.
        if ($prevX !== null && $prevY !== null) {
            $xEdge = $right;
            if ($xEdge > $prevX) {
                imageline($image, (int) $prevX, (int) $prevY, (int) $xEdge, (int) $prevY, $textcolor);
            }
        }

        ob_start();
        imagepng($image);
        $rawImageBytes = ob_get_clean();

        // rawImageBytes is PNG data
        return "<img src='data:image/png;base64," . base64_encode($rawImageBytes) . "' />";
    }

    /**
     * Creates a yearly chart with the magnitude of the target per day.
     * For planets subclasses should implement `magnitude(Carbon $date): float`.
     * We sample every day of the year and draw a connected line. Brighter
     * magnitudes (smaller numbers) are drawn toward the top of the chart.
     *
     * @param  GeographicalCoordinates  $geo_coords  The geographical coordinates
     * @param  Carbon  $date  A representative date (year used)
     * @return string The generated chart as an embedded image
     */
    public function yearMagnitudeGraph(GeographicalCoordinates $geo_coords, Carbon $date, bool $debug = false): string
    {
        $image = imagecreatetruecolor(1000, 400);

        // Background black
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 1000, 400, $black);

        $textcolor = imagecolorallocate($image, 255, 255, 255);
        $axiscolor = imagecolorallocate($image, 150, 150, 150);

        $left = 70;
        $right = 958;
        $width = $right - $left;

        $year = $date->year;
        $yearStart = Carbon::create($year, 1, 1, 12, 0, 0, $date->timezone);
        $daysInYear = $yearStart->isLeapYear() ? 366 : 365;

        // Collect magnitudes first (daily sampling)
        $samples = [];
        $debugLog = [];
        for ($day = 1; $day <= $daysInYear; $day++) {
            $sample = $yearStart->copy()->addDays($day - 1);
            $mag = null;
            try {
                $mag = $this->magnitude($sample);
            } catch (\Throwable $e) {
                $mag = null;
                if ($debug) {
                    $debugLog[] = sprintf("day %d (%s): exception: %s", $day, $sample->toDateString(), $e->getMessage());
                }
            }

            // treat sentinel/fallback values as missing
            if ($mag === null || !is_finite($mag) || $mag === 99.9 || abs($mag) > 50) {
                // missing
                if ($debug) {
                    $debugLog[] = sprintf("day %d (%s): mag=%s (skipped)", $day, $sample->toDateString(), var_export($mag, true));
                }
                continue;
            } else {
                if ($debug && count($debugLog) < 20) {
                    $debugLog[] = sprintf("day %d (%s): mag=%s", $day, $sample->toDateString(), var_export($mag, true));
                }
            }

            $doy = $sample->dayOfYear;
            $frac = ($doy - 1) / $daysInYear;
            $x = (int) ($left + $frac * $width);
            $samples[] = ['doy' => $doy, 'x' => $x, 'mag' => $mag];
        }

        // If we found no valid samples, fall back to stored magnitude once per month
        if (empty($samples)) {
            for ($m = 1; $m <= 12; $m++) {
                $sample = Carbon::create($year, $m, 15, 12, 0, 0, $date->timezone);
                $mag = $this->getMagnitude();
                if ($mag === null || !is_finite($mag) || $mag === 99.9) continue;
                $doy = $sample->dayOfYear;
                $frac = ($doy - 1) / $daysInYear;
                $x = (int) ($left + $frac * $width);
                $samples[] = ['doy' => $doy, 'x' => $x, 'mag' => $mag];
            }
        }

        // If still empty, return a small placeholder image
        if (empty($samples)) {
            if ($debug && !empty($debugLog)) {
                // render debug log lines onto the image for quick inspection
                $y = 20;
                imagestring($image, 3, 10, 2, 'Debug: magnitude sampling (first lines)', $textcolor);
                foreach (array_slice($debugLog, 0, 18) as $line) {
                    imagestring($image, 2, 10, $y, $line, $textcolor);
                    $y += 16;
                }
            } else {
                imagestring($image, 3, 200, 180, 'No magnitude data available', $textcolor);
            }
            ob_start();
            imagepng($image);
            $rawImageBytes = ob_get_clean();
            return "<img src='data:image/png;base64," . base64_encode($rawImageBytes) . "' />";
        }

        // Determine dynamic mag range and pad slightly
        $mags = array_column($samples, 'mag');
        $magMin = min($mags);
        $magMax = max($mags);
        // Add padding (invert because lower numbers are brighter)
        $pad = max(0.1, ($magMax - $magMin) * 0.05);
        $magMin = $magMin - $pad;
        $magMax = $magMax + $pad;
        if ($magMin == $magMax) {
            $magMin -= 1.0;
            $magMax += 1.0;
        }

        $yTop = 5;
        $yBottom = 365;

        // Draw month labels/ticks
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        for ($m = 0; $m < 12; $m++) {
            $firstOfMonth = Carbon::create($year, $m + 1, 1, 12, 0, 0, $date->timezone);
            $doy = $firstOfMonth->dayOfYear;
            $frac = ($doy - 1) / $daysInYear;
            $x = (int) ($left + $frac * $width);
            imagestring($image, 2, $x - 10, 370, $monthNames[$m], $textcolor);
            imageline($image, $x, 365, $x, 355, $axiscolor);
        }
        imagestring($image, 2, $right - 10, 370, 'Jan', $textcolor);
        imageline($image, $right, 365, $right, 355, $axiscolor);

        // Y axis labels (show top, middle, bottom)
        imagestring($image, 2, 35, 360, sprintf('%.1f', $magMax), $textcolor);
        imageline($image, $left, 365, $right, 365, $axiscolor);
        $mid1 = $magMin + ($magMax - $magMin) * 0.33;
        $mid2 = $magMin + ($magMax - $magMin) * 0.66;
        imagestring($image, 2, 35, 240, sprintf('%.1f', $mid2), $textcolor);
        imageline($image, $left, 245, $right, 245, $axiscolor);
        imagestring($image, 2, 35, 120, sprintf('%.1f', $mid1), $textcolor);
        imageline($image, $left, 125, $right, 125, $axiscolor);
        imagestring($image, 2, 35, 0, sprintf('%.1f', $magMin), $textcolor);
        imageline($image, $left, 5, $right, 5, $axiscolor);

        // Draw connected points and small markers
        $prevX = null;
        $prevY = null;
        foreach ($samples as $s) {
            $x = $s['x'];
            $mag = $s['mag'];
            $clamped = max($magMin, min($magMax, $mag));
            $fracY = ($clamped - $magMin) / ($magMax - $magMin);
            $y = (int) ($yTop + $fracY * ($yBottom - $yTop));

            if ($prevX !== null && $prevY !== null) {
                imageline($image, (int) $prevX, (int) $prevY, (int) $x, (int) $y, $textcolor);
            }
            // small dot marker
            imagefilledellipse($image, (int) $x, (int) $y, 4, 4, $textcolor);
            $prevX = $x;
            $prevY = $y;
        }

        ob_start();
        imagepng($image);
        $rawImageBytes = ob_get_clean();
        return "<img src='data:image/png;base64," . base64_encode($rawImageBytes) . "' />";
    }

    /**
     * Creates a yearly chart with the angular diameter (arcseconds) of the target per day.
     * For planet subclasses that implement `calculateDiameter(Carbon $date)` this
     * method will call that to populate `getDiameter()` for each sample. We sample
     * every day of the year and draw a connected line. Larger diameters (arcsec)
     * are drawn toward the top of the chart.
     *
     * @param  GeographicalCoordinates  $geo_coords  The geographical coordinates
     * @param  Carbon  $date  A representative date (year used)
     * @return string The generated chart as an embedded PNG image
     */
    public function yearDiameterGraph(GeographicalCoordinates $geo_coords, Carbon $date, bool $debug = false): string
    {
        $image = imagecreatetruecolor(1000, 400);

        // Background black
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, 1000, 400, $black);

        $textcolor = imagecolorallocate($image, 255, 255, 255);
        $axiscolor = imagecolorallocate($image, 150, 150, 150);

        $left = 70;
        $right = 958;
        $width = $right - $left;

        $year = $date->year;
        $yearStart = Carbon::create($year, 1, 1, 12, 0, 0, $date->timezone);
        $daysInYear = $yearStart->isLeapYear() ? 366 : 365;

        // Collect diameters (daily sampling)
        $samples = [];
        $debugLog = [];
        for ($day = 1; $day <= $daysInYear; $day++) {
            $sample = $yearStart->copy()->addDays($day - 1);

            // If subclass provides calculateDiameter(), call it to populate getDiameter().
            try {
                if (method_exists($this, 'calculateDiameter')) {
                    $this->calculateDiameter($sample);
                }
            } catch (\Throwable $e) {
                if ($debug) {
                    $debugLog[] = sprintf("day %d (%s): calculateDiameter exception: %s", $day, $sample->toDateString(), $e->getMessage());
                }
            }

            $diam = null;
            try {
                [$d1, $d2] = $this->getDiameter();
                $diam = $d1;
            } catch (\Throwable $e) {
                $diam = null;
            }

            // treat missing/non-finite values as absent
            if ($diam === null || !is_finite($diam) || $diam <= 0.0 || $diam > 1000000.0) {
                if ($debug) {
                    $debugLog[] = sprintf("day %d (%s): diam=%s (skipped)", $day, $sample->toDateString(), var_export($diam, true));
                }
                continue;
            }

            if ($debug && count($debugLog) < 20) {
                $debugLog[] = sprintf("day %d (%s): diam=%s arcsec", $day, $sample->toDateString(), $diam);
            }

            $doy = $sample->dayOfYear;
            $frac = ($doy - 1) / $daysInYear;
            $x = (int) ($left + $frac * $width);
            $samples[] = ['doy' => $doy, 'x' => $x, 'diam' => $diam];
        }

        // If we found no valid samples, fall back to stored diameter once per month
        if (empty($samples)) {
            for ($m = 1; $m <= 12; $m++) {
                $sample = Carbon::create($year, $m, 15, 12, 0, 0, $date->timezone);
                $d = $this->getDiameter();
                $mag = $d[0] ?? null;
                if ($mag === null || !is_finite($mag) || $mag <= 0.0) continue;
                $doy = $sample->dayOfYear;
                $frac = ($doy - 1) / $daysInYear;
                $x = (int) ($left + $frac * $width);
                $samples[] = ['doy' => $doy, 'x' => $x, 'diam' => $mag];
            }
        }

        // If still empty, render placeholder (optionally with debug)
        if (empty($samples)) {
            if ($debug && !empty($debugLog)) {
                $y = 20;
                imagestring($image, 3, 10, 2, 'Debug: diameter sampling (first lines)', $textcolor);
                foreach (array_slice($debugLog, 0, 18) as $line) {
                    imagestring($image, 2, 10, $y, $line, $textcolor);
                    $y += 16;
                }
            } else {
                imagestring($image, 3, 200, 180, 'No diameter data available', $textcolor);
            }
            ob_start();
            imagepng($image);
            $rawImageBytes = ob_get_clean();
            return "<img src='data:image/png;base64," . base64_encode($rawImageBytes) . "' />";
        }

        // Determine dynamic diameter range and pad slightly
        $diams = array_column($samples, 'diam');
        $diamMin = min($diams);
        $diamMax = max($diams);
        $pad = max(0.01, ($diamMax - $diamMin) * 0.05);
        $diamMin = $diamMin - $pad;
        $diamMax = $diamMax + $pad;
        if ($diamMin == $diamMax) {
            $diamMin = max(0.0, $diamMin - 1.0);
            $diamMax = $diamMax + 1.0;
        }

        $yTop = 5;
        $yBottom = 365;

        // Draw month labels/ticks
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        for ($m = 0; $m < 12; $m++) {
            $firstOfMonth = Carbon::create($year, $m + 1, 1, 12, 0, 0, $date->timezone);
            $doy = $firstOfMonth->dayOfYear;
            $frac = ($doy - 1) / $daysInYear;
            $x = (int) ($left + $frac * $width);
            imagestring($image, 2, $x - 10, 370, $monthNames[$m], $textcolor);
            imageline($image, $x, 365, $x, 355, $axiscolor);
        }
        imagestring($image, 2, $right - 10, 370, 'Jan', $textcolor);
        imageline($image, $right, 365, $right, 355, $axiscolor);

        // Y axis labels with largest diameters at the top.
        // Top (largest)
        imagestring($image, 2, 35, $yTop - 2, sprintf('%.2f"', $diamMax), $textcolor);
        imageline($image, $left, $yTop, $right, $yTop, $axiscolor);
        // Upper-middle
        $mid2 = $diamMin + ($diamMax - $diamMin) * 0.66;
        imagestring($image, 2, 35, (int) ($yTop + ($yBottom - $yTop) * 0.33) - 2, sprintf('%.2f"', $mid2), $textcolor);
        imageline($image, $left, (int) ($yTop + ($yBottom - $yTop) * 0.33), $right, (int) ($yTop + ($yBottom - $yTop) * 0.33), $axiscolor);
        // Lower-middle
        $mid1 = $diamMin + ($diamMax - $diamMin) * 0.33;
        imagestring($image, 2, 35, (int) ($yTop + ($yBottom - $yTop) * 0.66) - 2, sprintf('%.2f"', $mid1), $textcolor);
        imageline($image, $left, (int) ($yTop + ($yBottom - $yTop) * 0.66), $right, (int) ($yTop + ($yBottom - $yTop) * 0.66), $axiscolor);
        // Bottom (smallest)
        imagestring($image, 2, 35, $yBottom +  -5, sprintf('%.2f"', $diamMin), $textcolor);
        imageline($image, $left, $yBottom, $right, $yBottom, $axiscolor);

        // Draw connected points and small markers. Larger diameters toward top.
        $prevX = null;
        $prevY = null;
        foreach ($samples as $s) {
            $x = $s['x'];
            $diam = $s['diam'];
            $clamped = max($diamMin, min($diamMax, $diam));
            $fracY = ($clamped - $diamMin) / ($diamMax - $diamMin);
            // invert so larger diam => smaller y (toward top)
            $y = (int) ($yBottom - $fracY * ($yBottom - $yTop));

            if ($prevX !== null && $prevY !== null) {
                imageline($image, (int) $prevX, (int) $prevY, (int) $x, (int) $y, $textcolor);
            }
            imagefilledellipse($image, (int) $x, (int) $y, 4, 4, $textcolor);
            $prevX = $x;
            $prevY = $y;
        }

        ob_start();
        imagepng($image);
        $rawImageBytes = ob_get_clean();
        return "<img src='data:image/png;base64," . base64_encode($rawImageBytes) . "' />";
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
     * Fallback magnitude method. Planet subclasses implement a public
     * `magnitude(Carbon $date): float` method; this protected stub provides
     * a safe default and satisfies static analysis.
     */
    protected function magnitude(Carbon $date): float
    {
        // Return stored magnitude when available, otherwise use a faint sentinel
        return $this->_magnitude ?? 99.9;
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

    /**
     * Subclasses may override to calculate apparent equatorial coordinates for a given date.
     * Default implementation is a no-op to allow calling code to remain generic.
     *
     * @param  Carbon  $date
     */
    protected function calculateApparentEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // no-op; subclasses (e.g. Moon, Planet) provide a real implementation
    }

    /**
     * Subclasses may override to calculate topocentric equatorial coordinates for a given date.
     * Default implementation is a no-op to allow calling code to remain generic.
     *
     * @param  Carbon  $date
     * @param  GeographicalCoordinates  $geo_coords
     * @param  float  $height
     */
    protected function calculateEquatorialCoordinates(Carbon $date, ...$args): void
    {
        // no-op; subclasses (e.g. Sun, Moon, Planet) provide a real implementation
    }
}
