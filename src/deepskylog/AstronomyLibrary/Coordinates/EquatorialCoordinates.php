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
                tan(deg2rad($phi)) * cos(deg2rad($this->getDeclination()->getCoordinate()))
                - sin(deg2rad($this->getDeclination()->getCoordinate())) * cos(deg2rad($H))
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
}
