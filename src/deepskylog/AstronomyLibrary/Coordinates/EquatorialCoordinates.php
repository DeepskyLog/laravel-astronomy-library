<?php

/**
 * EquatorialCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Coordinates;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Models\ConstellationBoundaries;
use deepskylog\AstronomyLibrary\Time;

/**
 * EquatorialCoordinates class.
 *
 * PHP Version 7
 *
 * @category Coordinates
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @see     http://www.deepskylog.org
 */
class EquatorialCoordinates
{
    private Coordinate $_ra;
    private Coordinate $_decl;
    private float $_epoch = 2000.0;
    private float $_deltaRA = 0.0;
    private float $_deltaDec = 0.0;

    /**
     * The constructor.
     *
     * @param float $ra The right ascension (0, 24)
     * @param float ination The declination (-90, 90)
     * @param float $epoch    The epoch of the target (2000.0 is standard)
     * @param float $deltaRA  The proper motion in Right Ascension in seconds/year
     * @param float $deltaDec The proper motion in declination in ''/year
     */
    public function __construct(
        float $ra,
        float $declination,
        float $epoch = 2000.0,
        float $deltaRA = 0.0,
        float $deltaDec = 0.0
    ) {
        $this->setRA($ra);
        $this->setDeclination($declination);
        $this->setEpoch($epoch);
        $this->setDeltaRA($deltaRA);
        $this->setDeltaDec($deltaDec);
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
        $this->_ra = new Coordinate($ra, 0.0, 24.0);
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
        $this->_decl = new Coordinate($declination, -90.0, 90.0);
    }

    /**
     * Sets the epoch.
     *
     * @param float $epoch The epoch
     *
     * @return None
     */
    public function setEpoch(float $epoch): void
    {
        $this->_epoch = $epoch;
    }

    /**
     * Sets the proper motion in RA.
     *
     * @param float $deltaRA the proper motion in RA is seconds/year
     *
     * @return None
     */
    public function setDeltaRA(float $deltaRA): void
    {
        $this->_deltaRA = $deltaRA;
    }

    /**
     * Sets the proper motion in declination.
     *
     * @param float $deltaDec the proper motion in declination in ''/year
     *
     * @return None
     */
    public function setDeltaDec(float $deltaDec): void
    {
        $this->_deltaDec = $deltaDec;
    }

    /**
     * Gets the Right Ascension.
     *
     * @return Coordinate the Right Ascension in decimal hours
     */
    public function getRA(): Coordinate
    {
        return $this->_ra;
    }

    /**
     * Gets the declination.
     *
     * @return Coordinate The declination in degrees
     */
    public function getDeclination(): Coordinate
    {
        return $this->_decl;
    }

    /**
     * Gets the epoch.
     *
     * @return float The epoch
     */
    public function getEpoch(): float
    {
        return $this->_epoch;
    }

    /**
     * Gets the the proper motion in RA.
     *
     * @return float The proper motion in RA in seconds/year
     */
    public function getDeltaRA(): float
    {
        return $this->_deltaRA;
    }

    /**
     * Gets the the proper motion in declination.
     *
     * @return float The proper motion in declination in ''/year
     */
    public function getDeltaDec(): float
    {
        return $this->_deltaDec;
    }

    /**
     * Returns a readable string of the declination.
     *
     * @return string A readable string of the declination in degrees,
     *                minutes, seconds
     */
    public function printDeclination(): string
    {
        return $this->getDeclination()->convertToDegrees();
    }

    /**
     * Returns a readable string of the Right Ascension.
     *
     * @return string A readable string of the right ascension in hours,
     *                minutes, seconds
     */
    public function printRA(): string
    {
        return $this->getRA()->convertToHours();
    }

    /**
     * Converts the equatorial coordinates to ecliptical coordinates in
     * the current equinox.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @param float $nutObliquity The nutation in obliquity
     *
     * @return EclipticalCoordinates The ecliptical coordinates
     */
    public function convertToEcliptical(float $nutObliquity): EclipticalCoordinates
    {
        $longitude = rad2deg(
            atan2(
                sin(deg2rad($this->_ra->getCoordinate() * 15.0)) *
                cos(deg2rad($nutObliquity))
                + tan(deg2rad($this->_decl->getCoordinate())) *
                sin(deg2rad($nutObliquity)),
                cos(deg2rad($this->_ra->getCoordinate() * 15.0))
            )
        );

        $latitude = rad2deg(
            asin(
                sin(deg2rad($this->_decl->getCoordinate()))
                * cos(deg2rad($nutObliquity))
                - cos(deg2rad($this->_decl->getCoordinate()))
                * sin(deg2rad($nutObliquity)) *
                sin(deg2rad($this->_ra->getCoordinate() * 15.0))
            )
        );

        return new EclipticalCoordinates($longitude, $latitude);
    }

    /**
     * Converts the equatorial coordinates to ecliptical coordinates in
     * the J2000 equinox.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @return EclipticalCoordinates The ecliptical coordinates
     */
    public function convertToEclipticalJ2000(): EclipticalCoordinates
    {
        return $this->convertToEcliptical(23.4392911);
    }

    /**
     * Converts the equatorial coordinates to ecliptical coordinates in
     * the B1950 equinox.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @return EclipticalCoordinates The ecliptical coordinates
     */
    public function convertToEclipticalB1950(): EclipticalCoordinates
    {
        return $this->convertToEcliptical(23.4457889);
    }

    /**
     * Converts the equatorial coordinates to local horizontal coordinates.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @param GeographicalCoordinates $geo_coords    the geographical
     *                                               coordinates
     * @param Carbon                  $siderial_time the local siderial time
     *
     * @return HorizontalCoordinates The horizontal coordinates
     */
    public function convertToHorizontal(
        GeographicalCoordinates $geo_coords,
        Carbon $siderial_time
    ): HorizontalCoordinates {
        // Latitude of the observer
        $phi = $geo_coords->getLatitude()->getCoordinate();
        $H = $this->getHourAngle($siderial_time);

        $azimuth = rad2deg(
            atan2(
                sin(deg2rad($H)),
                cos(deg2rad($H)) * sin(deg2rad($phi))
                - tan(deg2rad($this->getDeclination()->getCoordinate()))
                * cos(deg2rad($phi))
            )
        );

        $height = rad2deg(
            asin(
                sin(deg2rad($phi))
                * sin(deg2rad($this->getDeclination()->getCoordinate()))
                + cos(deg2rad($phi))
                * cos(deg2rad($this->getDeclination()->getCoordinate()))
                * cos(deg2rad($H))
            )
        );

        return new HorizontalCoordinates($azimuth, $height);
    }

    /**
     * Converts the equatorial coordinates to galactic coordinates.
     * Chapter 13 of Astronomical Algorithms.
     *
     * @return GalacticCoordinates The galactic coordinates
     */
    public function convertToGalactic(): GalacticCoordinates
    {
        $ra = $this->getRA()->getCoordinate() * 15.0;
        $decl = $this->getDeclination()->getCoordinate();

        $l = rad2deg(
            atan2(
                cos(deg2rad($decl)) * sin(deg2rad($ra - 192.85948)),
                sin(deg2rad($decl)) * cos(deg2rad(27.12825))
                - cos(deg2rad($decl)) * sin(deg2rad(27.12825))
                * cos(deg2rad($ra - 192.85948))
            )
        );

        $b = rad2deg(
            asin(
                sin(deg2rad($decl)) * sin(deg2rad(27.12825)) +
                cos(deg2rad($decl)) * cos(deg2rad(27.12825))
                * cos(deg2rad($ra - 192.85948))
            )
        );

        return new GalacticCoordinates(122.93192 - $l, $b);
    }

    /**
     * Returns the parallactic angle of the object. The parallactic angle is
     * negative before and positive after the passage throught the southern
     * meridian. This is the effect of the moon that is lying down at moonrise.
     * Chapter 14 of Astronomical Algorithms.
     *
     * @param GeographicalCoordinates $geo_coords    the geographical
     *                                               coordinates
     * @param Carbon                  $siderial_time the local siderial time
     *
     * @return float The parallactic angle q
     */
    public function getParallacticAngle(
        GeographicalCoordinates $geo_coords,
        Carbon $siderial_time
    ): float {
        $phi = $geo_coords->getLatitude()->getCoordinate();
        $H = $this->getHourAngle($siderial_time);

        $q = rad2deg(
            atan2(
                sin(deg2rad($H)),
                tan(deg2rad($phi))
                * cos(deg2rad($this->getDeclination()->getCoordinate()))
                - sin(deg2rad($this->getDeclination()->getCoordinate()))
                * cos(deg2rad($H))
            )
        );

        return $q;
    }

    /**
     * Returns the local hour angle.
     *
     * @param Carbon $siderial_time The siderial time
     *
     * @return float the local hour angle
     */
    public function getHourAngle(Carbon $siderial_time): float
    {
        // Local hour angle = local siderial time - ra
        $sid = ((
            ($siderial_time->milliseconds / 1000.0) + $siderial_time->second
        ) / 60.0 + $siderial_time->minute) / 60 + $siderial_time->hour;

        return ($sid - $this->getRA()->getCoordinate()) * 15.0;
    }

    /**
     * Returns the angular separation between these coordinates and other
     * equatorial coordinates.
     * Chapter 17 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords2 the coordinates of the second object
     *
     * @return Coordinate The angular separation between the two objects
     */
    public function angularSeparation(
        self $coords2
    ): Coordinate {
        $d = rad2deg(
            acos(
                sin(deg2rad($this->getDeclination()->getCoordinate())) *
                sin(deg2rad($coords2->getDeclination()->getCoordinate()))
                + cos(deg2rad($this->getDeclination()->getCoordinate())) *
                cos(deg2rad($coords2->getDeclination()->getCoordinate())) *
                cos(
                    deg2rad(
                        $this->getRA()->getCoordinate() * 15.0
                        - $coords2->getRA()->getCoordinate() * 15.0
                    )
                )
            )
        );

        if ($d < 0.16) {
            $d = sqrt(
                (
                    $this->getRA()->getCoordinate() * 15.0
                    - $coords2->getRA()->getCoordinate() * 15.0
                ) * cos(
                    deg2rad(
                        (
                            $this->getDeclination()->getCoordinate()
                            + $coords2->getDeclination()->getCoordinate()
                        ) / 2
                    )
                ) ** 2 +
                (
                    $this->getDeclination()->getCoordinate()
                    - $coords2->getDeclination()->getCoordinate()
                )
            );
        }

        return new Coordinate($d);
    }

    /**
     * Returns true if the three bodies are in a straight line.
     * Chapter 19 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords2   The coordinates of the second object
     * @param EquatorialCoordinates $coords3   The coordinates of the thirds object
     * @param float                 $threshold The threshold for the method
     *                                         (default value is 10e-06)
     *
     * @return bool True if the three bodies are in a straight line
     */
    public function isInStraightLine(
        self $coords2,
        self $coords3,
        float $threshold = 1e-6
    ): bool {
        $result = tan(deg2rad($this->getDeclination()->getCoordinate())) *
                sin(
                    deg2rad(
                        $coords2->getRA()->getCoordinate() * 15.0
                        - $coords3->getRA()->getCoordinate() * 15.0
                    )
                ) + tan(deg2rad($coords2->getDeclination()->getCoordinate())) *
                sin(
                    deg2rad(
                        $coords3->getRA()->getCoordinate() * 15.0
                        - $this->getRA()->getCoordinate() * 15.0
                    )
                ) + tan(deg2rad($coords3->getDeclination()->getCoordinate())) *
                sin(
                    deg2rad(
                        $this->getRA()->getCoordinate() * 15.0
                        - $coords2->getRA()->getCoordinate() * 15.0
                    )
                );

        if (abs($result) < $threshold) {
            return true;
        }

        return false;
    }

    /**
     * Returns the deviation from a straight line.
     * Chapter 19 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords2 The coordinates of the first object
     * @param EquatorialCoordinates $coords3 The coordinates of the second object
     *
     * @return Coordinate the deviation from the straight line
     */
    public function deviationFromStraightLine(
        self $coords2,
        self $coords3
    ): Coordinate {
        $X1 = cos(deg2rad($coords2->getDeclination()->getCoordinate())) *
                    cos(deg2rad($coords2->getRA()->getCoordinate() * 15.0));
        $Y1 = cos(deg2rad($coords2->getDeclination()->getCoordinate())) *
                    sin(deg2rad($coords2->getRA()->getCoordinate() * 15.0));
        $Z1 = sin(deg2rad($coords2->getDeclination()->getCoordinate()));

        $X2 = cos(deg2rad($coords3->getDeclination()->getCoordinate())) *
                    cos(deg2rad($coords3->getRA()->getCoordinate() * 15.0));
        $Y2 = cos(deg2rad($coords3->getDeclination()->getCoordinate())) *
                    sin(deg2rad($coords3->getRA()->getCoordinate() * 15.0));
        $Z2 = sin(deg2rad($coords3->getDeclination()->getCoordinate()));

        $A = $Y1 * $Z2 - $Z1 * $Y2;
        $B = $Z1 * $X2 - $X1 * $Z2;
        $C = $X1 * $Y2 - $Y1 * $X2;

        $m = tan(deg2rad($this->getRA()->getCoordinate() * 15.0));
        $n = tan(deg2rad($this->getDeclination()->getCoordinate()))
                    / cos(deg2rad($this->getRA()->getCoordinate() * 15.0));

        $omega = rad2deg(
            abs(
                asin(
                    ($A + $B * $m + $C * $n) /
                    (sqrt($A * $A + $B * $B + $C * $C) * sqrt(1 + $m * $m + $n * $n))
                )
            )
        );

        return new Coordinate($omega, 0.0, 90.0);
    }

    /**
     * Returns the smallest circle containing three celestial bodies.
     * Chapter 20 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords2 The coordinates of the second object
     * @param EquatorialCoordinates $coords3 The coordinates of the third object
     *
     * @return Coordinate the diameter of the smallest circle
     */
    public function smallestCircle(
        self $coords2,
        self $coords3
    ): Coordinate {
        $dist[0] = $this->angularSeparation($coords2)->getCoordinate();
        $dist[1] = $this->angularSeparation($coords3)->getCoordinate();
        $dist[2] = $coords2->angularSeparation($coords3)->getCoordinate();

        rsort($dist);

        $a = $dist[0];
        $b = $dist[1];
        $c = $dist[2];

        if ($a > sqrt($b * $b + $c * $c)) {
            return new Coordinate($a);
        } else {
            return new Coordinate(
                2 * $a * $b * $c /
                sqrt(
                    ($a + $b + $c) * ($a + $b - $c)
                    * ($b + $c - $a) * ($a + $c - $b)
                )
            );
        }
    }

    /**
     * Returns the precession: the coordinates for another epoch and equinox.
     * Chapter 21 of Astronomical Algorithms.
     *
     * @param Carbon $date The date for the new equinox
     *
     * @return EquatorialCoordinates the precessed coordinates
     */
    public function precession(Carbon $date): EquatorialCoordinates
    {
        $precessed_coordinates = clone $this;

        if ($date->isLeapYear()) {
            $year = $date->year + ($date->dayOfYear - 1.0) / 366;
        } else {
            $year = $date->year + ($date->dayOfYear - 1.0) / 365;
        }

        $T = ($this->getEpoch() - $year) / 100.0;
        $m = 3.07496 + 0.00186 * $T;
        $n = 20.0431 - 0.0085 * $T;

        $deltaRA = (
            $this->getDeltaRA() + (
                $m
                + $n * sin(deg2rad($this->getRA()->getCoordinate() * 15.0))
                * tan(deg2rad($this->getDeclination()->getCoordinate())) / 15.0
            )
        ) * ($year - $this->getEpoch());
        $deltaDecl = (
            $this->getDeltaDec() + $n
            * cos(deg2rad($this->getRA()->getCoordinate() * 15.0))
        ) * ($year - $this->getEpoch());

        $precessed_coordinates->setRA(
            $this->getRA()->getCoordinate() + $deltaRA / 3600.0
        );
        $precessed_coordinates->setDeclination(
            $this->getDeclination()->getCoordinate() + $deltaDecl / 3600.0
        );

        return $precessed_coordinates;
    }

    /**
     * Calculate the coordinates including the proper motion.
     *
     * @param Carbon $date The Carbon data
     *
     * @return EquatorialCoordinates the coordinates including the proper motion for
     *                               the given date
     */
    private function _coordinatesWithProperMotion(
        Carbon $date
    ): EquatorialCoordinates {
        $coordinates = clone $this;

        $epoch_in_JD = Time::getJd(
            Carbon::create($this->getEpoch(), 1, 1, 12, 0, 0, 'UTC')
        );

        $jd = Time::getJd($date);

        $time_interval_starting_final = ($jd - $epoch_in_JD) / 36525.0;

        $ra_with_proper_motion = (
            $this->getRA()->getCoordinate()
            + $this->getDeltaRA() * $time_interval_starting_final * 100.0 / 3600.0
        );
        $dec_with_proper_motion = $this->getDeclination()->getCoordinate()
            + $this->getDeltaDec() * $time_interval_starting_final * 100.0 / 3600.0;

        $coordinates->setRA($ra_with_proper_motion);
        $coordinates->setDeclination($dec_with_proper_motion);

        return $coordinates;
    }

    /**
     * Returns the precession: the coordinates for another epoch and equinox.
     * Chapter 21 of Astronomical Algorithms.
     *
     * @param Carbon $date The date for the new equinox
     *
     * @return EquatorialCoordinates the precessed coordinates
     */
    public function precessionHighAccuracy(
        Carbon $date
    ): EquatorialCoordinates {
        $epoch_in_JD = Time::getJd(
            Carbon::create($this->getEpoch(), 1, 1, 12, 0, 0, 'UTC')
        );

        $time_interval_J2000_starting = ($epoch_in_JD - 2451545.0) / 36525.0;

        $jd = Time::getJd($date);

        $time_interval_starting_final = ($jd - $epoch_in_JD) / 36525.0;

        $precessed_coordinates = clone $this;

        $proper_motion_coordinates = $this->_coordinatesWithProperMotion($date);

        $ra_with_proper_motion = $proper_motion_coordinates
            ->getRA()->getCoordinate() * 15.0;
        $dec_with_proper_motion = $proper_motion_coordinates
            ->getDeclination()->getCoordinate();

        $ksi = (
            (2306.2181
                + 1.39656 * $time_interval_J2000_starting
                - 0.000139 * $time_interval_J2000_starting ** 2)
                * $time_interval_starting_final
            + (0.30188 - 0.000344 * $time_interval_J2000_starting)
                * $time_interval_starting_final ** 2
            + 0.017998
                * $time_interval_starting_final ** 3
        ) / 3600.0;

        $zeta = (
            (2306.2181
                + 1.39656 * $time_interval_J2000_starting
                - 0.000139 * $time_interval_J2000_starting ** 2)
                * $time_interval_starting_final
            + (1.09468 + 0.000066 * $time_interval_J2000_starting)
                * $time_interval_starting_final ** 2
            + 0.018203
                * $time_interval_starting_final ** 3
        ) / 3600.0;

        $theta = (
            (2004.3109
                - 0.85330 * $time_interval_J2000_starting
                - 0.000217 * $time_interval_J2000_starting ** 2)
                * $time_interval_starting_final
            - (0.42665 + 0.000217 * $time_interval_J2000_starting)
                * $time_interval_starting_final ** 2
            - 0.041833
                * $time_interval_starting_final ** 3
        ) / 3600.0;

        $A = cos(deg2rad($dec_with_proper_motion))
            * sin(deg2rad($ra_with_proper_motion + $ksi));
        $B = cos(deg2rad($theta)) * cos(deg2rad($dec_with_proper_motion))
            * cos(deg2rad($ra_with_proper_motion + $ksi))
            - sin(deg2rad($theta)) * sin(deg2rad($dec_with_proper_motion));
        $C = sin(deg2rad($theta)) * cos(deg2rad($dec_with_proper_motion))
            * cos(deg2rad($ra_with_proper_motion + $ksi))
            + cos(deg2rad($theta)) * sin(deg2rad($dec_with_proper_motion));

        $precessed_coordinates->setRA(
            (rad2deg(atan2($A, $B)) + $zeta) / 15.0
        );
        $precessed_coordinates->setDeclination(rad2deg(asin($C)));

        return $precessed_coordinates;
    }

    /**
     * Returns the apparent place of a star.
     * Chapter 23 of Astronomical Algorithms.
     *
     * @param Carbon $date     The date for the new equinox
     * @param array  $nutation The nutation for the given data
     *
     * @return EquatorialCoordinates the precessed coordinates
     */
    public function apparentPlace(
        Carbon $date,
        array $nutation
    ): EquatorialCoordinates {
        $epoch_in_JD = Time::getJd(
            Carbon::create($this->getEpoch(), 1, 1, 12, 0, 0, 'UTC')
        );

        $jd = Time::getJd($date);

        $time_interval_starting_final = ($jd - $epoch_in_JD) / 36525.0;

        $coordinates = $this->_coordinatesWithProperMotion($date);

        // Calculate the perturbations caused by the sun and the planets
        $L2 = 3.1761467 + 1021.3285546 * $time_interval_starting_final;
        $L3 = 1.7534703 + 628.3075849 * $time_interval_starting_final;
        $L4 = 6.2034809 + 334.0612431 * $time_interval_starting_final;
        $L5 = 0.5995465 + 52.9690965 * $time_interval_starting_final;
        $L6 = 0.8740168 + 21.3299095 * $time_interval_starting_final;
        $L7 = 5.4812939 + 7.4781599 * $time_interval_starting_final;
        $L8 = 5.3118863 + 3.8133036 * $time_interval_starting_final;
        $L_accent = 3.8103444 + 8399.6847337 * $time_interval_starting_final;
        $D = 5.1984667 + 7771.3771486 * $time_interval_starting_final;
        $M_accent = 2.3555559 + 8328.6914289 * $time_interval_starting_final;
        $F = 1.6279052 + 8433.4661601 * $time_interval_starting_final;

        $X_accent = (-1719914 - 2 * $time_interval_starting_final) * sin($L3)
            - 25 * cos($L3)
            + (6434 + 141 * $time_interval_starting_final) * sin(2 * $L3)
            + (28007 - 107 * $time_interval_starting_final) * cos(2 * $L3)
            + (715) * sin($L5)
            + (715) * sin($L_accent)
            + (486 - 5 * $time_interval_starting_final) * sin(3 * $L3)
            + (-236 - 4 * $time_interval_starting_final) * cos(3 * $L3)
            + (159) * sin($L6)
            + (39) * sin($L_accent + $M_accent)
            + (33) * sin(2 * $L5)
            + (-10) * cos(2 * $L5)
            + (31) * sin(2 * $L3 - $L5)
            + (1) * cos(2 * $L3 - $L5)
            + (8) * sin(3 * $L3 - 8 * $L4 + 3 * $L5)
            + (-28) * cos(3 * $L3 - 8 * $L4 + 3 * $L5)
            + (8) * sin(5 * $L3 - 8 * $L4 + 3 * $L5)
            + (-28) * cos(5 * $L3 - 8 * $L4 + 3 * $L5)
            + (21) * sin(2 * $L2 - $L3)
            + (-19) * sin($L2)
            + (17) * sin($L7)
            + (16) * sin($L3 - 2 * $L5)
            + (16) * sin($L8)
            + (11) * sin($L3 + $L5)
            + (-1) * cos($L3 + $L5)
            + (-11) * cos(2 * $L2 - 2 * $L3)
            + (-11) * sin($L3 - $L5)
            + (-2) * cos($L3 - $L5)
            + (-7) * sin(4 * $L3)
            + (-8) * cos(4 * $L3)
            + (-10) * sin(3 * $L3 - 2 * $L5)
            + (-9) * sin($L2 - 2 * $L3)
            + (-9) * sin(2 * $L2 - 3 * $L3)
            + (-9) * cos(2 * $L6)
            + (-9) * cos(2 * $L2 - 4 * $L3)
            + (8) * sin(3 * $L3 - 2 * $L4)
            + (8) * sin($L_accent + 2 * $D - $M_accent)
            + (-4) * sin(8 * $L2 - 12 * $L3)
            + (-7) * cos(8 * $L2 - 12 * $L3)
            + (-4) * sin(8 * $L2 - 14 * $L3)
            + (-7) * cos(8 * $L2 - 14 * $L3)
            + (-6) * sin(2 * $L4)
            + (-5) * cos(2 * $L4)
            + (-1) * sin(3 * $L2 - 4 * $L3)
            + (-1) * cos(3 * $L2 - 4 * $L3)
            + (4) * sin(2 * $L3 - 2 * $L5)
            + (-6) * cos(2 * $L3 - 2 * $L5)
            + (-7) * cos(3 * $L2 - 3 * $L3)
            + (5) * sin(2 * $L3 - 2 * $L4)
            + (-5) * cos(2 * $L3 - 2 * $L4)
            + (5) * sin($L_accent - 2 * $D);

        $Y_accent = (25 - 13 * $time_interval_starting_final) * sin($L3)
            + (1578089 + 156 * $time_interval_starting_final) * cos($L3)
            + (25697 - 95 * $time_interval_starting_final) * sin(2 * $L3)
            + (-5904 - 130 * $time_interval_starting_final) * cos(2 * $L3)
            + (6) * sin($L5)
            + (-657) * cos($L5)
            + (-656) * cos($L_accent)
            + (-216 - 4 * $time_interval_starting_final) * sin(3 * $L3)
            + (-446 - 5 * $time_interval_starting_final) * cos(3 * $L3)
            + (2) * sin($L6)
            + (-147) * cos($L6)
            + (26) * cos($F)
            + (-36) * cos($L_accent + $M_accent)
            + (-9) * sin(2 * $L5)
            + (-30) * cos(2 * $L5)
            + (1) * sin(2 * $L3 - $L5)
            + (-28) * cos(2 * $L3 - $L5)
            + (25) * sin(3 * $L3 - 8 * $L4 + 3 * $L5)
            + (8) * cos(3 * $L3 - 8 * $L4 + 3 * $L5)
            + (-25) * sin(5 * $L3 - 8 * $L4 + 3 * $L5)
            + (-8) * cos(5 * $L3 - 8 * $L4 + 3 * $L5)
            + (-19) * cos(2 * $L2 - $L3)
            + (17) * cos($L2)
            + (-16) * cos($L7)
            + (15) * cos($L3 - 2 * $L5)
            + (1) * sin($L8)
            + (-15) * cos($L8)
            + (-1) * sin($L3 + $L5)
            + (-10) * cos($L3 + $L5)
            + (-10) * sin(2 * $L2 - 2 * $L3)
            + (-2) * sin($L3 - $L5)
            + (9) * cos($L3 - $L5)
            + (-8) * sin(4 * $L3)
            + (6) * cos(4 * $L3)
            + (9) * cos(3 * $L3 - 2 * $L5)
            + (-9) * cos($L2 - 2 * $L3)
            + (-8) * cos(2 * $L2 - 3 * $L3)
            + (-8) * sin(2 * $L6)
            + (8) * sin(2 * $L2 - 4 * $L3)
            + (-8) * cos(3 * $L3 - 2 * $L4)
            + (-7) * cos($L_accent + 2 * $D - $M_accent)
            + (-6) * sin(8 * $L2 - 12 * $L3)
            + (4) * cos(8 * $L2 - 12 * $L3)
            + (6) * sin(8 * $L2 - 14 * $L3)
            + (-4) * cos(8 * $L2 - 14 * $L3)
            + (-4) * sin(2 * $L4)
            + (5) * cos(2 * $L4)
            + (-2) * sin(3 * $L2 - 4 * $L3)
            + (-7) * cos(3 * $L2 - 4 * $L3)
            + (-5) * sin(2 * $L3 - 2 * $L5)
            + (-4) * cos(2 * $L3 - 2 * $L5)
            + (-6) * sin(3 * $L2 - 3 * $L3)
            + (-4) * sin(2 * $L3 - 2 * $L4)
            + (-5) * cos(2 * $L3 - 2 * $L4)
            + (-5) * cos($L_accent - 2 * $D);

        $Z_accent = (10 + 32 * $time_interval_starting_final) * sin($L3)
            + (684185 - 358 * $time_interval_starting_final) * cos($L3)
            + (11141 - 48 * $time_interval_starting_final) * sin(2 * $L3)
            + (-2559 - 55 * $time_interval_starting_final) * cos(2 * $L3)
            + (-15) * sin($L5)
            + (-282) * cos($L5)
            + (-285) * cos($L_accent)
            + (-94) * sin(3 * $L3)
            + (-193) * cos(3 * $L3)
            + (-6) * sin($L6)
            + (-61) * cos($L6)
            + (-59) * cos($F)
            + (-16) * cos($L_accent + $M_accent)
            + (-5) * sin(2 * $L5)
            + (-13) * cos(2 * $L5)
            + (-12) * cos(2 * $L3 - $L5)
            + (11) * sin(3 * $L3 - 8 * $L4 + 3 * $L5)
            + (3) * cos(3 * $L3 - 8 * $L4 + 3 * $L5)
            + (-11) * sin(5 * $L3 - 8 * $L4 + 3 * $L5)
            + (-3) * cos(5 * $L3 - 8 * $L4 + 3 * $L5)
            + (-8) * cos(2 * $L2 - $L3)
            + (8) * cos($L2)
            + (-7) * cos($L7)
            + (1) * sin($L3 - 2 * $L5)
            + (7) * cos($L3 - 2 * $L5)
            + (-3) * sin($L8)
            + (-6) * cos($L8)
            + (-1) * sin($L3 + $L5)
            + (-5) * cos($L3 + $L5)
            + (-4) * sin(2 * $L2 - 2 * $L3)
            + (-1) * sin($L3 - $L5)
            + (4) * cos($L3 - $L5)
            + (-3) * sin(4 * $L3)
            + (3) * cos(4 * $L3)
            + (4) * cos(3 * $L3 - 2 * $L5)
            + (-4) * cos($L2 - 2 * $L3)
            + (-4) * cos(2 * $L2 - 3 * $L3)
            + (-3) * sin(2 * $L6)
            + (3) * sin(2 * $L2 - 4 * $L3)
            + (-3) * cos(3 * $L3 - 2 * $L4)
            + (-3) * cos($L_accent + 2 * $D - $M_accent)
            + (-3) * sin(8 * $L2 - 12 * $L3)
            + (2) * cos(8 * $L2 - 12 * $L3)
            + (3) * sin(8 * $L2 - 14 * $L3)
            + (-2) * cos(8 * $L2 - 14 * $L3)
            + (-2) * sin(2 * $L4)
            + (-2) * cos(2 * $L4)
            + (1) * sin(3 * $L2 - 4 * $L3)
            + (-4) * cos(3 * $L2 - 4 * $L3)
            + (-2) * sin(2 * $L3 - 2 * $L5)
            + (-2) * cos(2 * $L3 - 2 * $L5)
            + (-3) * sin(3 * $L2 - 3 * $L3)
            + (-2) * sin(2 * $L3 - 2 * $L4)
            + (-2) * cos(2 * $L3 - 2 * $L4)
            + (-2) * cos($L_accent - 2 * $D);

        $delta_ra = rad2deg(
            (
                $Y_accent
                * cos(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
                - $X_accent
                * sin(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
            )
            / (
                17314463350
            * cos(deg2rad($coordinates->getDeclination()->getCoordinate()))
            )
        );

        $delta_dec = -rad2deg(
            (
                (
                    $X_accent
                    * cos(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
            + $Y_accent * sin(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
                ) * sin(deg2rad($coordinates->getDeclination()->getCoordinate()))
            - $Z_accent
            * cos(deg2rad($coordinates->getDeclination()->getCoordinate()))
            ) / 17314463350
        );

        // Add to coordinates
        $coordinates->setRA(
            ($coordinates->getRA()->getCoordinate() * 15.0 + $delta_ra) / 15.0
        );

        $coordinates->setDeclination(
            $coordinates->getDeclination()->getCoordinate() + $delta_dec
        );

        // Calculate the precession (but don't take into account the proper motion,
        // we already did this)
        $precessed_coordinates = new EquatorialCoordinates(
            $coordinates->getRA()->getCoordinate(),
            $coordinates->getDeclination()->getCoordinate(),
            $coordinates->getEpoch(),
            0.0,
            0.0
        );

        $coordinates = $precessed_coordinates->precessionHighAccuracy($date, false);

        // Add effect of nutation
        $delta_ra = (
            cos(deg2rad($nutation[3])) + sin(deg2rad($nutation[3])) * sin(
                deg2rad($coordinates->getRA()->getCoordinate() * 15.0)
            ) * tan(deg2rad($coordinates->getDeclination()->getCoordinate()))
        ) * $nutation[0]
            - (cos(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
            * tan(deg2rad($coordinates->getDeclination()->getCoordinate())))
            * $nutation[1];

        $delta_dec = sin(deg2rad($nutation[3]))
            * cos(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
            * $nutation[0]
            + sin(deg2rad($coordinates->getRA()->getCoordinate() * 15.0))
            * $nutation[1];

        $coordinates->setRA(
            ($coordinates->getRA()->getCoordinate() * 15.0 + $delta_ra / 3600.0)
             / 15.0
        );

        $coordinates->setDeclination(
            $coordinates->getDeclination()->getCoordinate() + $delta_dec / 3600.0
        );

        return $coordinates;
    }

    /**
     * Returns the constellation from the given coordinates.
     *
     * @return string The constellation (3-character code in Latin for example: ERI, LEO, LMI, ...)
     */
    public function getConstellation(): string
    {
        $tempdecl = -90;
        $tempcon = 'OCT';
        $thera0 = 0.0;
        $thera1 = 0.0;
        $thedecl0 = 0.0;
        $thedecl1 = 0.0;

        foreach (ConstellationBoundaries::all() as $boundaries) {
            $thera0 = $boundaries->ra0;
            $thera1 = $boundaries->ra1;
            $thedecl0 = $boundaries->decl0;
            $thedecl1 = $boundaries->decl1;

            if (abs($thera0 - $thera1) > 12) {
                if (abs($this->getRA()->getCoordinate() - $thera0) > 12) {
                    $thera0 += (($thera0 < 12) ? 24.0 : -24.0);
                } else {
                    $thera1 += (($thera1 < 12) ? 24.0 : -24.0);
                }
            }

            if (abs($thera1 - $thera0) > 0) {
                $thedecl01 = $thedecl0 + (($this->getRA()->getCoordinate() - $thera0) / ($thera1 - $thera0) * ($thedecl1 - $thedecl0));
            } else {
                $thedecl01 = ($thedecl0 + $thedecl1) / 2;
            }
            if (
                (
                    $thera0 <= $this->getRA()->getCoordinate()
                        && ($thera1 >= $this->getRA()->getCoordinate())
                        || ($thera1 <= $this->getRA()->getCoordinate())
                        && ($thera0 >= $this->getRA()->getCoordinate())
                ) && (
                    $thedecl01 < $this->getDeclination()->getCoordinate()
                ) && ($thedecl01 > $tempdecl)
            ) {
                $tempdecl = $thedecl01;
                if ($boundaries->con0pos == 'A') {
                    $tempcon = $boundaries->con0;
                }
                if ($boundaries->con0pos == 'B') {
                    $tempcon = $boundaries->con1;
                }
                if ($boundaries->con0pos == 'L') {
                    if ((($thedecl1 - $thedecl0) / ($thera1 - $thera0)) > 0) {
                        $tempcon = $boundaries->con1;
                    } else {
                        $tempcon = $boundaries->con0;
                    }
                }
                if ($boundaries->con0pos == 'R') {
                    if ((($thedecl1 - $thedecl0) / ($thera1 - $thera0)) > 0) {
                        $tempcon = $boundaries->con0;
                    } else {
                        $tempcon = $boundaries->con1;
                    }
                }
            }
        }

        return $tempcon;
    }

    /**
     * Returns the page in the astronomical atlas.
     *
     * @param string $atlas The requested atlas. Possible atlases are
     *                      milleniumbase  : The Millenium Star Atlas
     *                      urano          : Uranometria First Edition
     *                      urano_new      : Uranometria Second Edition
     *                      psa            : The Pocket Sky Atlas
     *                      sky            : The Sky atlas
     *                      taki           : The Taki atlas
     *                      torresB        : The B atlas of Torres
     *                      torresBC       : The BC atlas of Torres
     *                      torresC        : The C atlas of Torres
     *                      DeepskyHunter  : The DeepskyHunter atlas
     *                      Interstellarum : The Interstellarum Deep Sky Atlas (IDSA)
     *                      DSLOP          : The Overview atlas of DeepskyLog (in portrait)
     *                      DSLLP          : The Lookup atlas of DeepskyLog (in portrait)
     *                      DSLDP          : The Detail atlas of DeepskyLog (in portrait)
     *                      DSLOL          : The Overview atlas of DeepskyLog (in landscape)
     *                      DSLLL          : The Lookup atlas of DeepskyLog (in landscape)
     *                      DSLDL          : The Detail atlas of DeepskyLog (in landscape)
     *
     * @return string the atlas page
     */
    public function calculateAtlasPage(string $atlas): string
    {
        switch ($atlas) {
            case 'milleniumbase':
                return $this->calculateMilleniumPage();
                break;
            case 'urano_new':
                return $this->calculateNewUranometriaPage();
                break;
            case 'psa':
                return $this->calculatePocketSkyAtlasPage();
                break;
            case 'sky':
                return $this->calculateSkyAtlasPage();
                break;
            case 'taki':
                return $this->calculateTakiPage();
                break;
            case 'torresB':
                return $this->calculateTorresBPage();
                break;
            case 'torresBC':
                return $this->calculateTorresBCPage();
                break;
            case 'torresC':
                return $this->calculateTorresCPage();
                break;
            case 'urano':
                return $this->calculateUranometriaPage();
                break;
            case 'DSLOP':
                return $this->calculateDSL(0);
                break;
            case 'DSLLP':
                return $this->calculateDSL(1);
                break;
            case 'DSLDP':
                return $this->calculateDSL(2);
                break;
            case 'DSLOL':
                return $this->calculateDSL(3);
                break;
            case 'DSLLL':
                return $this->calculateDSL(4);
                break;
            case 'DSLDL':
                return $this->calculateDSL(5);
                break;
            case 'DeepskyHunter':
                return $this->calculateDeepskyHunter();
                break;
            case 'Interstellarum':
                return $this->calculateInterstellarum();
                break;
            default:
                return $this->calculateUranometriaPage();
        }
    }

    private function calculateDSL($atlastype)
    {
        $atlaspages = [[12, 20, 26, 30, 32, 32, 30, 26, 20, 12],                          // overview
                                [12, 20, 30, 36, 42, 48, 52, 54, 54, 54, 52, 48, 42, 36, 30, 20, 12],     // lookup
                            [12, 21, 31, 40, 48, 55, 63, 70, 77, 83, 89, 94, 98, 101, 104, 106, 107, 107, 107, 107, 106, 104, 101, 98, 94, 89, 83, 77, 70, 63, 55, 48, 40, 31, 21, 12], // Detail
                            [6, 10, 13, 16, 19, 20, 21, 21, 21, 20, 19, 16, 13, 10, 6],             // overview landscape
                            [6, 10, 13, 17, 21, 24, 27, 30, 32, 34, 35, 36, 36, 36, 36, 36, 35, 34, 32, 30, 27, 24, 21, 17, 13, 10, 6], // lookup landscape
                            [6, 10, 14, 18, 22, 26, 30, 33, 37, 41, 44, 47, 50, 53, 56, 58, 61, 63, 65, 66, 68, 70, 70, 71, 71, 72, 72, 72, 71, 71, 70, 70, 68, 66, 65, 63, 61, 58, 56, 53, 50, 47, 44, 41, 37, 33, 30, 26, 22, 18, 14, 10, 6], // detail landscape
                           ];
        $page = 1;
        for ($i = 0; $i < count($atlaspages[$atlastype]); ++$i) {
            if (
                ($this->getDeclination()->getCoordinate() < (90 + ((-$i) * (180 / (count($atlaspages[$atlastype]))))))
                && ($this->getDeclination()->getCoordinate() >= (90 + ((-1 - $i) * (180 / (count($atlaspages[$atlastype]))))))
            ) {
                return $page + floor((24 - $this->getRA()->getCoordinate()) / (24 / ($atlaspages[$atlastype][$i])));
            } else {
                $page += $atlaspages[$atlastype][$i];
            }
        }

        return 0;
    }

    private function calculateMilleniumPage()
    {
        $rao = $this->getRA()->getCoordinate();
        $ra = $this->getRA()->getCoordinate();
        $pa = 0;
        $qt = 0;
        $qn = 0;

        if (abs($this->getDeclination()->getCoordinate()) > 87) {
            $ra = 0;
        }
        if ($ra >= 0 && $ra <= 8) {
            $vol = 'I';
            $vl = 0;
        }
        if ($ra > 8 && $ra <= 16) {
            $vol = 'II';
            $vl = 1;
        }
        if ($ra > 16 && $ra < 24) {
            $vol = 'III';
            $vl = 2;
        }
        if (abs($this->getDeclination()->getCoordinate()) <= 90) {
            $pa = 240;
            $qt = $qt + 2;
            $qn = 2;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 87) {
            $pa = 120;
            $qt = $qt + 4;
            $qn = 4;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 81) {
            $pa = 60;
            $qt = $qt + 8;
            $qn = 8;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 75) {
            $pa = 48;
            $qt = $qt + 10;
            $qn = 10;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 69) {
            $pa = 40;
            $qt = $qt + 12;
            $qn = 12;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 63) {
            $pa = 480 / 14;
            $qt = $qt + 14;
            $qn = 14;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 57) {
            $pa = 30;
            $qt = $qt + 16;
            $qn = 16;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 51) {
            $pa = 24;
            $qt = $qt + 20;
            $qn = 20;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 45) {
            $pa = 24;
            $qt = $qt + 20;
            $qn = 20;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 39) {
            $pa = 480 / 22;
            $qt = $qt + 22;
            $qn = 22;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 33) {
            $pa = 480 / 22;
            $qt = $qt + 22;
            $qn = 22;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 27) {
            $pa = 20;
            $qt = $qt + 24;
            $qn = 24;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 21) {
            $pa = 20;
            $qt = $qt + 24;
            $qn = 24;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 15) {
            $pa = 20;
            $qt = $qt + 24;
            $qn = 24;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 9) {
            $pa = 20;
            $qt = $qt + 24;
            $qn = 24;
        }
        if (abs($this->getDeclination()->getCoordinate()) < 3) {
            $pa = 20;
            $qt = $qt + 24;
            $qn = 24;
        }
        if ($ra == 8) {
            $ra = 7.99;
        }
        if ($ra == 16) {
            $ra = 15.99;
        }
        if ($ra == 24) {
            $ra = 23.99;
        }
        if ($ra > $vl * 8) {
            $ra = $ra - ($vl * 8);
        }
        $ca = (int) (($ra * 60) / $pa);
        if (abs($this->getDeclination()->getCoordinate()) > 87 && ($rao > 4 && $rao < 16)) {
            $qt = 1;
            $qn = 0;
        }
        $ch = $qt - $ca + ($vl * 516);
        if ($this->getDeclination()->getCoordinate() < 0) {
            $ch = 516 + ($vl * 516) - $qt + $qn - $ca;
        }

        return $ch;
    }

    private function calculateNewUranometriaPage()
    {
        $data = [[84.5,   1,  1],  // 1st tier, chart 1
                [73.5,   7,  6],   // 2nd tier, charts 2->7
                [62.5,  17, 10],   // 3rd tier, charts 8->17
                [51.5,  29, 12],   // 4th tier, charts 18->29
                [40.5,  44, 15],   // 5th tier, charts 30->44
                [29.5,  62, 18],   // 6th tier, charts 45->62
                [17.5,  80, 18],   // 7th tier, charts 63->80
                [5.5, 100, 20],    // 8th tier, charts 81->100
                [-5.5, 120, 20],   // 9th tier, charts 101->120
                [-17.5, 140, 20],  // 10th tier, charts 121->140
                [-29.5, 158, 18],  // 11th tier, charts 141->158
                [-40.5, 176, 18],  // 12th tier, charts 159->176
                [-51.5, 191, 15],  // 13th tier, charts 177->191
                [-62.5, 203, 12],  // 14th tier, charts 192->203
                [-73.5, 213, 10],  // 15th tier, charts 204->213
                [-84.5, 219,  6],  // 16th tier, charts 214->219
                [-90.0, 220,  1], ]; // 17th tier, chart 220
        // find proper tier
        for ($Tier = 0; $this->getDeclination()->getCoordinate() < $data[$Tier][0]; ++$Tier);

        $HoursPerChart = 24.0 / $data[$Tier][2];
        $ra = $this->getRA()->getCoordinate() - ($HoursPerChart / 2);
        if ($ra < 0) {
            $ra += 24;
        }
        $MapOffset = (int) ($ra / $HoursPerChart);                                   // Offset; middle of 1st map is in the middle of 0 hours RA

        return (int) ($data[$Tier][1] - $MapOffset);
    }

    private function calculatePocketSkyAtlasPage()
    {
        $psa = 0;
        /* Page from pocket sky atlas */
        if ($this->getRA()->getCoordinate() >= 0.0 && $this->getRA()->getCoordinate() <= 3.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 1;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 1.5) {
                    $psa = 3;
                } else {
                    $psa = 2;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 1.5) {
                    $psa = 5;
                } else {
                    $psa = 4;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 1.5) {
                    $psa = 7;
                } else {
                    $psa = 6;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 1.5) {
                    $psa = 9;
                } else {
                    $psa = 8;
                }
            } else {
                $psa = 10;
            }
        } elseif ($this->getRA()->getCoordinate() >= 3.0 && $this->getRA()->getCoordinate() <= 6.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 11;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 4.5) {
                    $psa = 13;
                } else {
                    $psa = 12;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 4.5) {
                    $psa = 15;
                } else {
                    $psa = 14;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 4.5) {
                    $psa = 17;
                } else {
                    $psa = 16;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 4.5) {
                    $psa = 19;
                } else {
                    $psa = 18;
                }
            } else {
                $psa = 20;
            }
        } elseif ($this->getRA()->getCoordinate() >= 6.0 && $this->getRA()->getCoordinate() <= 9.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 21;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 7.5) {
                    $psa = 23;
                } else {
                    $psa = 22;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 7.5) {
                    $psa = 25;
                } else {
                    $psa = 24;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 7.5) {
                    $psa = 27;
                } else {
                    $psa = 26;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 7.5) {
                    $psa = 29;
                } else {
                    $psa = 28;
                }
            } else {
                $psa = 30;
            }
        } elseif ($this->getRA()->getCoordinate() >= 9.0 && $this->getRA()->getCoordinate() <= 12.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 31;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 10.5) {
                    $psa = 33;
                } else {
                    $psa = 32;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 10.5) {
                    $psa = 35;
                } else {
                    $psa = 34;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 10.5) {
                    $psa = 37;
                } else {
                    $psa = 36;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 10.5) {
                    $psa = 39;
                } else {
                    $psa = 38;
                }
            } else {
                $psa = 40;
            }
        } elseif ($this->getRA()->getCoordinate() >= 12.0 && $this->getRA()->getCoordinate() <= 15.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 41;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 13.5) {
                    $psa = 43;
                } else {
                    $psa = 42;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 13.5) {
                    $psa = 45;
                } else {
                    $psa = 44;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 13.5) {
                    $psa = 47;
                } else {
                    $psa = 46;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 13.5) {
                    $psa = 49;
                } else {
                    $psa = 48;
                }
            } else {
                $psa = 50;
            }
        } elseif ($this->getRA()->getCoordinate() >= 15.0 && $this->getRA()->getCoordinate() <= 18.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 51;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 16.5) {
                    $psa = 53;
                } else {
                    $psa = 52;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 16.5) {
                    $psa = 55;
                } else {
                    $psa = 54;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 16.5) {
                    $psa = 57;
                } else {
                    $psa = 56;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 16.5) {
                    $psa = 59;
                } else {
                    $psa = 58;
                }
            } else {
                $psa = 60;
            }
        } elseif ($this->getRA()->getCoordinate() >= 18.0 && $this->getRA()->getCoordinate() <= 21.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 61;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 19.5) {
                    $psa = 63;
                } else {
                    $psa = 62;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 19.5) {
                    $psa = 65;
                } else {
                    $psa = 64;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 19.5) {
                    $psa = 67;
                } else {
                    $psa = 66;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 19.5) {
                    $psa = 69;
                } else {
                    $psa = 68;
                }
            } else {
                $psa = 70;
            }
        } elseif ($this->getRA()->getCoordinate() >= 21.0) {
            if ($this->getDeclination()->getCoordinate() >= 60) {
                $psa = 71;
            } elseif ($this->getDeclination()->getCoordinate() >= 30) {
                if ($this->getRA()->getCoordinate() <= 22.5) {
                    $psa = 73;
                } else {
                    $psa = 72;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= 0) {
                if ($this->getRA()->getCoordinate() <= 22.5) {
                    $psa = 75;
                } else {
                    $psa = 74;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -30) {
                if ($this->getRA()->getCoordinate() <= 22.5) {
                    $psa = 77;
                } else {
                    $psa = 76;
                }
            } elseif ($this->getDeclination()->getCoordinate() >= -60) {
                if ($this->getRA()->getCoordinate() <= 22.5) {
                    $psa = 79;
                } else {
                    $psa = 78;
                }
            } else {
                $psa = 80;
            }
        }

        return (int) $psa;
    }

    private function calculateSkyAtlasPage()
    {
        $data = [[50.0,   1,  3],  // 1st tier, charts 1->3
                      [20.0,   4,  6],  // 2nd tier, charts 4->9
                      [-20.0,  10,  8],  // 3rd tier, charts 10->17
                      [-50.0,  18,  6],  // 4th tier, charts 18->23
                      [-90.0,  24,  3], ]; // 5th tier, charts 24->26
        // find proper tier
        for ($Tier = 0; $this->getDeclination()->getCoordinate() < $data[$Tier][0]; ++$Tier);
        $HoursPerChart = 24.0 / $data[$Tier][2];
        // Offset; middle of 1st map is in the middle of 0 hours RA
        $MapOffset = (int) ($this->getRA()->getCoordinate() / $HoursPerChart);

        return (int) ($data[$Tier][1] + $MapOffset);
    }

    private function calculateDeepskyHunter()
    {
        if ($this->getDeclination()->getCoordinate() >= 75.0) {
            if ($this->getRA()->getCoordinate() <= 12.0) {
                $dsh = 1;
            } else {
                $dsh = 2;
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 45.0) {
            $ratemp = $this->getRA()->getCoordinate() - (24.0 / 14.0 / 2.0);
            if ($ratemp < 0.0) {
                $ratemp += 24.0;
            }
            $diff = floor($ratemp / (24.0 / 14.0));
            $dsh = 16 - $diff;
        } elseif ($this->getDeclination()->getCoordinate() >= 17.5) {
            $ratemp = $this->getRA()->getCoordinate() - (24.0 / 20.0 / 2.0);
            if ($ratemp < 0.0) {
                $ratemp += 24.0;
            }
            $diff = floor($ratemp / (24.0 / 20.0));
            $dsh = 36 - $diff;
        } elseif ($this->getDeclination()->getCoordinate() >= -7.5) {
            $ratemp = $this->getRA()->getCoordinate() - (24.0 / 20.0 / 2.0);
            if ($ratemp < 0.0) {
                $ratemp += 24.0;
            }
            $diff = floor($ratemp / (24.0 / 20.0));
            $dsh = 56 - $diff;
        } elseif ($this->getDeclination()->getCoordinate() >= -35.0) {
            $ratemp = $this->getRA()->getCoordinate() - (24.0 / 20.0 / 2.0);
            if ($ratemp < 0.0) {
                $ratemp += 24.0;
            }
            $diff = floor($ratemp / (24.0 / 20.0));
            $dsh = 76 - $diff;
        } elseif ($this->getDeclination()->getCoordinate() >= -60.0) {
            $ratemp = $this->getRA()->getCoordinate() - (24.0 / 16.0 / 2.0);
            if ($ratemp < 0.0) {
                $ratemp += 24.0;
            }
            $diff = floor($ratemp / (24.0 / 16.0));
            $dsh = 92 - $diff;
        } elseif ($this->getDeclination()->getCoordinate() >= -90.0) {
            $ratemp = $this->getRA()->getCoordinate() - (24.0 / 9.0 / 2.0);
            if ($ratemp < 0.0) {
                $ratemp += 24.0;
            }
            $diff = floor($ratemp / (24.0 / 9.0));
            $dsh = 101 - $diff;
        }

        return $dsh;
    }

    private function calculateInterstellarum()
    {
        $data = [[82.0,   1,  1],  // 1st tier, chart 1
                      [67.0,   2,  6],  // 2nd tier, charts 2->7
                      [52.0,   8,  8],  // 3rd tier, charts 8->15
                      [37.0,  16,  12], // 4th tier, charts 16->27
                      [22.0,  28,  12], // 5th tier, charts 28->39
                      [7.0,  40,  12], // 6th tier, charts 40->51
                      [-7.0,  52,  12], // 7th tier, charts 52->63
                      [-22.0,  64,  12], // 8th tier, charts 64->75
                      [-37.0,  76,  12], // 9th tier, charts 76->87
                      [-52.0,  88,  12], // 10th tier, charts 88->99
                      [-67.0, 100,  8],  // 11th tier, charts 100->107
                      [-82.0, 108,  6],  // 12th tier, charts 108->113
                      [-90.0, 114,  1], ]; // 13th tier, chart 114
        // find proper tier
        for ($Tier = 0; $this->getDeclination()->getCoordinate() < $data[$Tier][0]; ++$Tier);
        $HoursPerChart = 24.0 / $data[$Tier][2];
        // Offset; middle of 1st map is in the middle of 0 hours RA
        $MapOffset = (int) ((24.0 - $this->getRA()->getCoordinate()) / $HoursPerChart);

        return (int) ($data[$Tier][1] + $MapOffset);
    }

    private function calculateTakiPage()
    {
        if ($this->getDeclination()->getCoordinate() >= 83) {
            $taki = 1;
        } elseif ($this->getDeclination()->getCoordinate() >= 62) {
            $taki = 2 + floor((24 - $this->getRA()->getCoordinate()) / 2);
        } elseif ($this->getDeclination()->getCoordinate() >= 37) {
            $taki = 14 + floor(24 - $this->getRA()->getCoordinate());
        } elseif ($this->getDeclination()->getCoordinate() >= 12) {
            $taki = 38 + floor(24 - $this->getRA()->getCoordinate());
        } elseif ($this->getDeclination()->getCoordinate() >= -12) {
            $taki = 62 + floor(24 - $this->getRA()->getCoordinate());
        } elseif ($this->getDeclination()->getCoordinate() >= -37) {
            $taki = 86 + floor(24 - $this->getRA()->getCoordinate());
        } elseif ($this->getDeclination()->getCoordinate() >= -62) {
            $taki = 110 + floor(24 - $this->getRA()->getCoordinate());
        } elseif ($this->getDeclination()->getCoordinate() >= -83) {
            $taki = 134 + floor((24 - $this->getRA()->getCoordinate()) / 2);
        } else {
            $taki = 146;
        }

        return $taki;
    }

    private function calculateTorresBPage()
    {
        $torresB = 0;
        /* Page from torres B atlas */
        if ($this->getDeclination()->getCoordinate() >= 64.28333) {
            if ($this->getRA()->getCoordinate() <= 1.2 || $this->getRA()->getCoordinate() >= 22.8) {
                $torresB = 1;
            } else {
                $torresB = 9 - (int) (($this->getRA()->getCoordinate() - 1.2) / 2.4);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 38.56666) {
            if ($this->getRA()->getCoordinate() <= 0.75 || $this->getRA()->getCoordinate() >= 23.25) {
                $torresB = 10;
            } else {
                $torresB = 25 - (int) (($this->getRA()->getCoordinate() - 0.75) / 1.5);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 12.85) {
            if ($this->getRA()->getCoordinate() <= 0.63166 || $this->getRA()->getCoordinate() >= 23.36833) {
                $torresB = 26;
            } else {
                $torresB = 44 - (int) (($this->getRA()->getCoordinate() - 0.63166) / 1.2633);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -12.85) {
            if ($this->getRA()->getCoordinate() <= 0.63166 || $this->getRA()->getCoordinate() >= 23.36833) {
                $torresB = 45;
            } else {
                $torresB = 63 - (int) (($this->getRA()->getCoordinate() - 0.63166) / 1.2633);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -38.56666) {
            if ($this->getRA()->getCoordinate() <= 0.63166 || $this->getRA()->getCoordinate() >= 23.36833) {
                $torresB = 64;
            } else {
                $torresB = 82 - (int) (($this->getRA()->getCoordinate() - 0.63166) / 1.2633);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -64.28333) {
            if ($this->getRA()->getCoordinate() <= 0.75 || $this->getRA()->getCoordinate() >= 23.25) {
                $torresB = 83;
            } else {
                $torresB = 98 - (int) (($this->getRA()->getCoordinate() - 0.75) / 1.5);
            }
        } else {
            if ($this->getRA()->getCoordinate() <= 1.2 || $this->getRA()->getCoordinate() >= 22.8) {
                $torresB = 99;
            } else {
                $torresB = 107 - (int) (($this->getRA()->getCoordinate() - 1.2) / 2.4);
            }
        }

        return (int) $torresB;
    }

    private function calculateTorresBCPage()
    {
        $torresBC = 0;
        /* Page from torres BC atlas */
        if ($this->getDeclination()->getCoordinate() >= 72.0) {
            if ($this->getRA()->getCoordinate() <= 1.2 || $this->getRA()->getCoordinate() >= 22.8) {
                $torresBC = 1;
            } else {
                $torresBC = 10 - (int) (($this->getRA()->getCoordinate() - 1.2) / 2.4);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 54.0) {
            if ($this->getRA()->getCoordinate() <= 0.666 || $this->getRA()->getCoordinate() >= 23.333) {
                $torresBC = 11;
            } else {
                $torresBC = 28 - (int) (($this->getRA()->getCoordinate() - 0.666) / 1.33);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 36.0) {
            if ($this->getRA()->getCoordinate() <= 0.5 || $this->getRA()->getCoordinate() >= 23.5) {
                $torresBC = 29;
            } else {
                $torresBC = 52 - (int) (($this->getRA()->getCoordinate() - 0.5) / 1.0);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 18.0) {
            if ($this->getRA()->getCoordinate() <= 0.42833 || $this->getRA()->getCoordinate() >= 23.57166) {
                $torresBC = 53;
            } else {
                $torresBC = 80 - (int) (($this->getRA()->getCoordinate() - 0.42833) / 0.85666);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 0.0) {
            if ($this->getRA()->getCoordinate() <= 0.41333 || $this->getRA()->getCoordinate() >= 23.5866) {
                $torresBC = 81;
            } else {
                $torresBC = 109 - (int) (($this->getRA()->getCoordinate() - 0.41333) / 0.82666);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -18.0) {
            if ($this->getRA()->getCoordinate() <= 0.41333 || $this->getRA()->getCoordinate() >= 23.5866) {
                $torresBC = 110;
            } else {
                $torresBC = 138 - (int) (($this->getRA()->getCoordinate() - 0.41333) / 0.82666);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -36.0) {
            if ($this->getRA()->getCoordinate() <= 0.42833 || $this->getRA()->getCoordinate() >= 23.57166) {
                $torresBC = 139;
            } else {
                $torresBC = 166 - (int) (($this->getRA()->getCoordinate() - 0.42833) / 0.85666);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -54.0) {
            if ($this->getRA()->getCoordinate() <= 0.5 || $this->getRA()->getCoordinate() >= 23.5) {
                $torresBC = 167;
            } else {
                $torresBC = 190 - (int) (($this->getRA()->getCoordinate() - 0.5) / 1.0);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -72.0) {
            if ($this->getRA()->getCoordinate() <= 0.6666 || $this->getRA()->getCoordinate() >= 23.3333) {
                $torresBC = 191;
            } else {
                $torresBC = 208 - (int) (($this->getRA()->getCoordinate() - 0.6666) / 1.3333);
            }
        } else {
            if ($this->getRA()->getCoordinate() <= 1.2 || $this->getRA()->getCoordinate() >= 22.8) {
                $torresBC = 209;
            } else {
                $torresBC = 218 - (int) (($this->getRA()->getCoordinate() - 1.2) / 2.4);
            }
        }

        return (int) $torresBC;
    }

    private function calculateTorresCPage()
    {
        $torresC = 0;
        /* Page from torres C atlas */
        if ($this->getDeclination()->getCoordinate() >= 79.0) {
            if ($this->getRA()->getCoordinate() <= 1.2 || $this->getRA()->getCoordinate() >= 22.8) {
                $torresC = 1;
            } else {
                $torresC = 10 - (int) (($this->getRA()->getCoordinate() - 1.2) / 2.4);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 69.0) {
            if ($this->getRA()->getCoordinate() <= 0.666 || $this->getRA()->getCoordinate() >= 23.333) {
                $torresC = 11;
            } else {
                $torresC = 28 - (int) (($this->getRA()->getCoordinate() - 0.666) / 1.33);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 58.0) {
            if ($this->getRA()->getCoordinate() <= 0.4616 || $this->getRA()->getCoordinate() >= 23.5383) {
                $torresC = 29;
            } else {
                $torresC = 54 - (int) (($this->getRA()->getCoordinate() - 0.4616) / 0.9233);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 48.0) {
            if ($this->getRA()->getCoordinate() <= 0.3633 || $this->getRA()->getCoordinate() >= 23.6366) {
                $torresC = 55;
            } else {
                $torresC = 87 - (int) (($this->getRA()->getCoordinate() - 0.3633) / 0.7266);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 37.0) {
            if ($this->getRA()->getCoordinate() <= 0.315 || $this->getRA()->getCoordinate() >= 23.685) {
                $torresC = 88;
            } else {
                $torresC = 125 - (int) (($this->getRA()->getCoordinate() - 0.315) / 0.630);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 27.0) {
            if ($this->getRA()->getCoordinate() <= 0.2783 || $this->getRA()->getCoordinate() >= 23.7216) {
                $torresC = 126;
            } else {
                $torresC = 168 - (int) (($this->getRA()->getCoordinate() - 0.2783) / 0.5566);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 16.0) {
            if ($this->getRA()->getCoordinate() <= 0.2616 || $this->getRA()->getCoordinate() >= 23.7383) {
                $torresC = 169;
            } else {
                $torresC = 214 - (int) (($this->getRA()->getCoordinate() - 0.2616) / 0.5233);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= 5.0) {
            if ($this->getRA()->getCoordinate() <= 0.25 || $this->getRA()->getCoordinate() >= 23.75) {
                $torresC = 215;
            } else {
                $torresC = 262 - (int) (($this->getRA()->getCoordinate() - 0.25) / 0.5);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -5.0) {
            if ($this->getRA()->getCoordinate() <= 0.255 || $this->getRA()->getCoordinate() >= 23.745) {
                $torresC = 263;
            } else {
                $torresC = 309 - (int) (($this->getRA()->getCoordinate() - 0.255) / 0.51);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -16.0) {
            if ($this->getRA()->getCoordinate() <= 0.25 || $this->getRA()->getCoordinate() >= 23.75) {
                $torresC = 310;
            } else {
                $torresC = 357 - (int) (($this->getRA()->getCoordinate() - 0.25) / 0.5);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -26.0) {
            if ($this->getRA()->getCoordinate() <= 0.2616 || $this->getRA()->getCoordinate() >= 23.7383) {
                $torresC = 358;
            } else {
                $torresC = 403 - (int) (($this->getRA()->getCoordinate() - 0.2616) / 0.5233);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -37.0) {
            if ($this->getRA()->getCoordinate() <= 0.2783 || $this->getRA()->getCoordinate() >= 23.7216) {
                $torresC = 404;
            } else {
                $torresC = 446 - (int) (($this->getRA()->getCoordinate() - 0.2783) / 0.5566);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -47.0) {
            if ($this->getRA()->getCoordinate() <= 0.315 || $this->getRA()->getCoordinate() >= 23.685) {
                $torresC = 447;
            } else {
                $torresC = 484 - (int) (($this->getRA()->getCoordinate() - 0.315) / 0.63);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -58.0) {
            if ($this->getRA()->getCoordinate() <= 0.3633 || $this->getRA()->getCoordinate() >= 23.6366) {
                $torresC = 485;
            } else {
                $torresC = 517 - (int) (($this->getRA()->getCoordinate() - 0.3633) / 0.7266);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -68.0) {
            if ($this->getRA()->getCoordinate() <= 0.4616 || $this->getRA()->getCoordinate() >= 23.5383) {
                $torresC = 518;
            } else {
                $torresC = 543 - (int) (($this->getRA()->getCoordinate() - 0.4616) / 0.9233);
            }
        } elseif ($this->getDeclination()->getCoordinate() >= -79.0) {
            if ($this->getRA()->getCoordinate() <= 0.666 || $this->getRA()->getCoordinate() >= 23.333) {
                $torresC = 544;
            } else {
                $torresC = 561 - (int) (($this->getRA()->getCoordinate() - 0.666) / 1.33);
            }
        } else {
            if ($this->getRA()->getCoordinate() <= 1.2 || $this->getRA()->getCoordinate() >= 22.8) {
                $torresC = 562;
            } else {
                $torresC = 571 - (int) (($this->getRA()->getCoordinate() - 1.2) / 2.4);
            }
        }

        return (int) $torresC;
    }

    private function calculateUranometriaPage()
    {
        $urano = 0;

        /* Page from uranometria */
        /* 90 to 85 */
        if ($this->getDeclination()->getCoordinate() >= 85) {
            if ($this->getRA()->getCoordinate() < 12) {
                $urano = 1;
            } else {
                $urano = 2;
            }
        }

        /* 84 to 73 */
        elseif ($this->getDeclination()->getCoordinate() >= 73) {
            if (($this->getRA()->getCoordinate() >= 1) && ($this->getRA()->getCoordinate() < 23)) {
                $urano = (int) $this->getRA()->getCoordinate() - 1;
                $urano = $urano / 2;
                $urano = $urano + 4;
            } else {
                $urano = 3;
            }
        }

        /* 72 to 61 */
        elseif ($this->getDeclination()->getCoordinate() >= 61) {
            $hulp = (int) $this->getRA()->getCoordinate() * 60;
            if (($hulp >= 32) && ($hulp < 1400)) {
                $urano = (($hulp - 32) / 72) + 16;
            } else {
                $urano = 15;
            }
        }

        /* 60 to 50 */
        elseif ($this->getDeclination()->getCoordinate() >= 50) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 28) && ($hulp < 1408)) {
                $urano = (($hulp - 28) / 60) + 36;
            } else {
                $urano = 35;
            }
        }

        /* 49 to 39 */
        elseif ($this->getDeclination()->getCoordinate() >= 39) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 24) && ($hulp < 1416)) {
                $urano = (($hulp - 24) / 48) + 60;
            } else {
                $urano = 59;
            }
        }

        /* 38 to 28 */
        elseif ($this->getDeclination()->getCoordinate() >= 28) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 20) && ($hulp < 1420)) {
                $urano = (($hulp - 20) / 40) + 90;
            } else {
                $urano = 89;
            }
        }

        /* 27 to 17 */
        elseif ($this->getDeclination()->getCoordinate() >= 17) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 16) && ($hulp < 1424)) {
                $urano = (($hulp - 16) / 32) + 126;
            } else {
                $urano = 125;
            }
        }

        /* 16 to 6 */
        elseif ($this->getDeclination()->getCoordinate() >= 6) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 16) && ($hulp < 1424)) {
                $urano = (($hulp - 16) / 32) + 171;
            } else {
                $urano = 170;
            }
        }

        /* 5 to -5 */
        elseif ($this->getDeclination()->getCoordinate() >= -5) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 16) && ($hulp < 1424)) {
                $urano = (($hulp - 16) / 32) + 216;
            } else {
                $urano = 215;
            }
        }

        /* -16 to -6 */
        elseif ($this->getDeclination()->getCoordinate() >= -16) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 16) && ($hulp < 1424)) {
                $urano = (($hulp - 16) / 32) + 261;
            } else {
                $urano = 260;
            }
        }

        /* -27 to -17 */
        elseif ($this->getDeclination()->getCoordinate() >= -27) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 16) && ($hulp < 1424)) {
                $urano = (($hulp - 16) / 32) + 306;
            } else {
                $urano = 305;
            }
        }

        /* -38 to -28 */
        elseif ($this->getDeclination()->getCoordinate() >= -38) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 20) && ($hulp < 1420)) {
                $urano = (($hulp - 20) / 40) + 351;
            } else {
                $urano = 350;
            }
        }

        /* -49 to -39 */
        elseif ($this->getDeclination()->getCoordinate() >= -49) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 24) && ($hulp < 1416)) {
                $urano = (($hulp - 24) / 48) + 387;
            } else {
                $urano = 386;
            }
        }

        /* -60 to -50 */
        elseif ($this->getDeclination()->getCoordinate() >= -60) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 28) && ($hulp < 1408)) {
                $urano = (($hulp - 28) / 60) + 417;
            } else {
                $urano = 416;
            }
        }

        /* -72 to -61 */
        elseif ($this->getDeclination()->getCoordinate() >= -72) {
            $hulp = (int) ($this->getRA()->getCoordinate() * 60.0);
            if (($hulp >= 32) && ($hulp < 1400)) {
                $urano = (($hulp - 32) / 72) + 441;
            } else {
                $urano = 440;
            }
        }

        /* -84 to -73 */
        elseif ($this->getDeclination()->getCoordinate() >= -84) {
            if (($this->getRA()->getCoordinate() >= 1.0) && ($this->getRA()->getCoordinate() < 23.0)) {
                $urano = (int) ($this->getRA()->getCoordinate()) - 1;
                $urano = $urano / 2;
                $urano = $urano + 461;
            } else {
                $urano = 460;
            }
        }

        /* -90 to -85 */
        else {
            if ($this->getRA()->getCoordinate() < 12.0) {
                $urano = 473;
            } else {
                $urano = 472;
            }
        }

        return (int) $urano;
    }
}
