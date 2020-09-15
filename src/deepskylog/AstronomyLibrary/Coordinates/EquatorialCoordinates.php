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

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Time;

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
     * @param float $ra          The right ascension (0, 24)
     * @param float $declination The declination (-90, 90)
     * @param float $epoch       The epoch of the target (2000.0 is standard)
     * @param float $deltaRA     The proper motion in Right Ascension in seconds/year
     * @param float $deltaDec    The proper motion in declination in ''/year
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
}
