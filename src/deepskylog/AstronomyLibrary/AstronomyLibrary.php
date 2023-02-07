<?php
/**
 * The main AstronomyLibrary class.
 *
 * PHP Version 8
 *
 * @category AstronomyLibrary
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GalacticCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\HorizontalCoordinates;

/**
 * The main AstronomyLibrary class.
 *
 * PHP Version 8
 *
 * @category AstronomyLibrary
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class AstronomyLibrary
{
    private Carbon $_date;
    private GeographicalCoordinates $_coordinates;
    private array $_nutation;
    private float $_jd;
    private Carbon $_siderialTime;
    private ?string $_lengthOfNightChart = null;
    private ?float $_deltaT = null;
    private float $_height = 0.0;
    private ?array $_earthsGlobe = null;

    /**
     * The constructor.
     *
     * @param Carbon                  $carbonDate  The date
     * @param GeographicalCoordinates $coordinates The geographical coordinates
     * @param float                   $height      The height of the location
     */
    public function __construct(
        Carbon $carbonDate,
        GeographicalCoordinates $coordinates,
        float $height = 0.0
    ) {
        $this->_date = $carbonDate;
        $this->_coordinates = $coordinates;
        $this->_jd = Time::getJd($this->_date);
        $this->_nutation = Time::nutation($this->getJd());
        $this->_siderialTime = Time::apparentSiderialTime(
            $this->_date,
            $this->_coordinates
        );
        $this->_deltaT = Time::deltaT($this->_date);
        $this->_height = $height;
    }

    /**
     * Returns the date and time.
     *
     * @return Carbon The Carbon date
     */
    public function getDate(): Carbon
    {
        return $this->_date;
    }

    /**
     * Sets the date and time.
     *
     * @param Carbon $date The new Carbon date
     *
     * @return None
     */
    public function setDate(Carbon $date): void
    {
        $this->_date = $date;
        $this->_jd = Time::getJd($this->_date);
        $this->_nutation = Time::nutation($this->getJd());
        $this->_siderialTime = Time::apparentSiderialTime(
            $this->_date,
            $this->_coordinates
        );
        $this->_lengthOfNightChart = null;
        $this->_deltaT = Time::deltaT($this->_date);
    }

    /**
     * Returns the geographical coordinates.
     *
     * @return GeographicalCoordinates The geographical coordinates
     */
    public function getGeographicalCoordinates(): GeographicalCoordinates
    {
        return $this->_coordinates;
    }

    /**
     * Sets the date and time.
     *
     * @param GeographicalCoordinates $coordinates The new geographical coordinates
     *
     * @return None
     */
    public function setGeographicalCoordinates(
        GeographicalCoordinates $coordinates
    ): void {
        $this->_coordinates = $coordinates;
        $this->_siderialTime = Time::apparentSiderialTime(
            $this->_date,
            $this->_coordinates
        );
        $this->_lengthOfNightChart = null;
        $this->_earthsGlobe = null;
    }

    /**
     * Returns the height of the location.
     *
     * @return float The height of the location
     */
    public function getHeight(): float
    {
        return $this->_height;
    }

    /**
     * Sets the height of the location in meters.
     *
     * @param float $height The height of the location in meters
     *
     * @return None
     */
    public function setHeight(
        float $height
    ): void {
        $this->_height = $height;
        $this->_earthsGlobe = null;
    }

    /**
     * Returns the julian day of the date.
     *
     * @return float The julian day
     */
    public function getJd(): float
    {
        return $this->_jd;
    }

    /**
     * Sets the julian day and adapt the date.
     *
     * @param float $jd The julian day
     *
     * @return None
     */
    public function setJd(float $jd): void
    {
        $this->_jd = $jd;
        $this->_date = Time::fromJd($jd);
        $this->_nutation = Time::nutation($this->getJd());
        $this->_siderialTime = Time::apparentSiderialTime(
            $this->_date,
            $this->_coordinates
        );
        $this->_deltaT = Time::deltaT($this->_date);

        $this->_lengthOfNightChart = null;
    }

    /**
     * Returns delta t of the date.
     *
     * @return float delta t
     */
    public function getDeltaT(): float
    {
        if ($this->_deltaT) {
            return $this->_deltaT;
        } else {
            return Time::deltaT($this->_date);
        }
    }

    /**
     * Returns dynamical time of the date.
     *
     * @return Carbon The dynamical time
     */
    public function getDynamicalTime(): Carbon
    {
        return Time::dynamicalTime($this->_date);
    }

    /**
     * Returns mean siderial time of the date.
     *
     * @return Carbon The siderial time
     */
    public function getMeanSiderialTime(): Carbon
    {
        return Time::meanSiderialTime($this->_date, $this->_coordinates);
    }

    /**
     * Returns apparent siderial time of the date.
     *
     * @return Carbon The siderial time
     */
    public function getApparentSiderialTime(): Carbon
    {
        return $this->_siderialTime;
    }

    /**
     * Returns nutation of the date.
     *
     * @return array The array with nutation in Longitude, nutation in Obliquity,
     *               mean Obliquity and true Obliquity
     */
    public function getNutation(): array
    {
        return $this->_nutation;
    }

    /**
     * Converts from Equatorial coordinates to ecliptical coordinates.
     *
     * @param EquatorialCoordinates $coords the equatorial coordinates to convert
     *
     * @return EclipticalCoordinates the ecliptical coordinates for J2000
     */
    public function equatorialToEcliptical(
        EquatorialCoordinates $coords
    ): EclipticalCoordinates {
        return $coords->convertToEclipticalJ2000();
    }

    /**
     * Converts from Ecliptical coordinates to Equatorial coordinates.
     *
     * @param EclipticalCoordinates $coords the ecliptical coordinates to convert
     *
     * @return EquatorialCoordinates The equatorial coordinates in J2000
     */
    public function eclipticalToEquatorial(
        EclipticalCoordinates $coords
    ): EquatorialCoordinates {
        return $coords->convertToEquatorialJ2000();
    }

    /**
     * Converts from Equatorial coordinates to horizontal coordinates.
     *
     * @param EquatorialCoordinates $coords the equatorial coordinates to convert
     *
     * @return HorizontalCoordinates the horizontal coordinates for the date and
     *                               location
     */
    public function equatorialToHorizontal(
        EquatorialCoordinates $coords
    ): HorizontalCoordinates {
        return $coords->convertToHorizontal(
            $this->getGeographicalCoordinates(),
            $this->getApparentSiderialTime()
        );
    }

    /**
     * Calculates the parallactic angle of an object. The parallactic angle is
     * negative before and positive after the passage throught the southern
     * meridian. This is the effect of the moon that is lying down at moonrise.
     *
     * @param EquatorialCoordinates $coords The coordinates of the object
     *
     * @return float the parallactic angle in degrees
     */
    public function parallacticAngle(EquatorialCoordinates $coords): float
    {
        return $coords->getParallacticAngle(
            $this->getGeographicalCoordinates(),
            $this->getApparentSiderialTime()
        );
    }

    /**
     * Converts from Horizontal coordinates to Equatorial coordinates.
     *
     * @param HorizontalCoordinates $coords the horizontal coordinates to convert
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function horizontalToEquatorial(
        HorizontalCoordinates $coords
    ): EquatorialCoordinates {
        return $coords->convertToEquatorial(
            $this->getGeographicalCoordinates(),
            $this->getApparentSiderialTime()
        );
    }

    /**
     * Converts from Equatorial coordinates to Galactic coordinates.
     *
     * @param EquatorialCoordinates $coords the equatorial coordinates to convert
     *
     * @return GalacticCoordinates The galactic coordinates (J2000)
     */
    public function equatorialToGalactic(
        EquatorialCoordinates $coords
    ): GalacticCoordinates {
        return $coords->convertToGalactic();
    }

    /**
     * Converts from Galactic coordinates to Equatorial coordinates.
     *
     * @param GalacticCoordinates $coords the galactic coordinates to convert
     *
     * @return EquatorialCoordinates The equatorial coordinates
     */
    public function galacticToEquatorial(
        GalacticCoordinates $coords
    ): EquatorialCoordinates {
        return $coords->convertToEquatorial();
    }

    /**
     * Creates a chart with the length of the year during the year.
     *
     * @param string $timezone the timezone to create the graph for
     *
     * @return string The chart with the length of the night
     */
    public function getLengthOfNightPlot($timezone): string
    {
        if (! $this->_lengthOfNightChart) {
            $date = Carbon::now();
            $date->year($this->getDate()->year);

            $image = imagecreatetruecolor(900, 400);

            $textcolor = imagecolorallocate($image, 255, 255, 255);
            $axiscolor = imagecolorallocate($image, 150, 150, 150);

            // Yellow = Day
            $daycolor = imagecolorallocate($image, 150, 150, 0);

            // Orange = Civil Twilight
            $civilcolor = imagecolorallocate($image, 225, 150, 0);

            // Blue = Nautical Twilight
            $nautcolor = imagecolorallocate($image, 0, 0, 250);

            // Dark blue = Astronomical Twilight
            $astrocolor = imagecolorallocate($image, 0, 0, 150);

            if ($date->isLeapYear()) {
                $length = 366 * 2 + 70;
            } else {
                $length = 365 * 2 + 70;
            }

            $runningday = 0;

            // TODO: Roosbeek!
            // TODO: Seiland, enkele dagen fout!
            for ($i = 0; $i < 12; $i++) {
                // Calculate the apparent siderial time
                imageline($image, 70 + $i * 61, 365, 70 + $i * 61, 355, $axiscolor);
                $date->day(1);
                $date->month($i + 1);

                for ($day = 1; $day <= $date->daysInMonth; $day++) {
                    $date->day($day);

                    $sun_info = date_sun_info(
                        $date->timestamp,
                        $this->getGeographicalCoordinates()->getLatitude()->getCoordinate(),
                        $this->getGeographicalCoordinates()->getLongitude()->getCoordinate()
                    );

                    if ($sun_info['sunrise'] === true) {
                        $sunriseHour = 0;
                        $sunriseMinute = 0;
                    } elseif ($sun_info['sunrise'] === false) {
                        $sunriseHour = 12;
                        $sunriseMinute = 0;
                    } else {
                        $sunriseHour = Carbon::createFromTimestamp(
                            $sun_info['sunrise']
                        )->timezone($timezone)->hour;
                        $sunriseMinute = Carbon::createFromTimestamp(
                            $sun_info['sunrise']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['civil_twilight_begin'] === true) {
                        $civilriseHour = 0;
                        $civilriseMinute = 0;
                    } elseif ($sun_info['civil_twilight_begin'] === false) {
                        $civilriseHour = 12;
                        $civilriseMinute = 0;
                    } else {
                        $civilriseHour = Carbon::createFromTimestamp(
                            $sun_info['civil_twilight_begin']
                        )->timezone($timezone)->hour;
                        $civilriseMinute = Carbon::createFromTimestamp(
                            $sun_info['civil_twilight_begin']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['nautical_twilight_begin'] === true) {
                        $nautriseHour = 0;
                        $nautriseMinute = 0;
                    } elseif ($sun_info['nautical_twilight_begin'] === false) {
                        $nautriseHour = 12;
                        $nautriseMinute = 0;
                    } else {
                        $nautriseHour = Carbon::createFromTimestamp(
                            $sun_info['nautical_twilight_begin']
                        )->timezone($timezone)->hour;
                        $nautriseMinute = Carbon::createFromTimestamp(
                            $sun_info['nautical_twilight_begin']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['astronomical_twilight_begin'] === true) {
                        $astroriseHour = 0;
                        $astroriseMinute = 0;
                    } elseif ($sun_info['astronomical_twilight_begin'] === false) {
                        $astroriseHour = 12;
                        $astroriseMinute = 0;
                    } else {
                        $astroriseHour = Carbon::createFromTimestamp(
                            $sun_info['astronomical_twilight_begin']
                        )->timezone($timezone)->hour;
                        $astroriseMinute = Carbon::createFromTimestamp(
                            $sun_info['astronomical_twilight_begin']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['sunset'] === true) {
                        $sunsetHour = 24;
                        $sunsetMinute = 0;
                    } elseif ($sun_info['sunset'] === false) {
                        $sunsetHour = 12;
                        $sunsetMinute = 0;
                    } else {
                        $sunsetHour = Carbon::createFromTimestamp(
                            $sun_info['sunset']
                        )->timezone($timezone)->hour;
                        $sunsetMinute = Carbon::createFromTimestamp(
                            $sun_info['sunset']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['civil_twilight_end'] === true) {
                        $civilsetHour = 24;
                        $civilsetMinute = 0;
                    } elseif ($sun_info['civil_twilight_end'] === false) {
                        $civilsetHour = 12;
                        $civilsetMinute = 0;
                    } else {
                        $civilsetHour = Carbon::createFromTimestamp(
                            $sun_info['civil_twilight_end']
                        )->timezone($timezone)->hour;
                        $civilsetMinute = Carbon::createFromTimestamp(
                            $sun_info['civil_twilight_end']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['nautical_twilight_end'] === true) {
                        $nautsetHour = 24;
                        $nautsetMinute = 0;
                    } elseif ($sun_info['nautical_twilight_end'] === false) {
                        $nautsetHour = 12;
                        $nautsetMinute = 0;
                    } else {
                        $nautsetHour = Carbon::createFromTimestamp(
                            $sun_info['nautical_twilight_end']
                        )->timezone($timezone)->hour;
                        $nautsetMinute = Carbon::createFromTimestamp(
                            $sun_info['nautical_twilight_end']
                        )->timezone($timezone)->minute;
                    }
                    if ($sun_info['astronomical_twilight_end'] === true) {
                        $astrosetHour = 24;
                        $astrosetMinute = 0;
                    } elseif ($sun_info['astronomical_twilight_end'] === false) {
                        $astrosetHour = 12;
                        $astrosetMinute = 0;
                    } else {
                        $astrosetHour = Carbon::createFromTimestamp(
                            $sun_info['astronomical_twilight_end']
                        )->timezone($timezone)->hour;
                        $astrosetMinute = Carbon::createFromTimestamp(
                            $sun_info['astronomical_twilight_end']
                        )->timezone($timezone)->minute;
                    }
                    if ($astrosetHour < 12) {
                        $astrosetHour += 24;
                    }
                    if ($nautsetHour < 12) {
                        $nautsetHour += 24;
                    }
                    if ($civilsetHour < 12) {
                        $civilsetHour += 24;
                    }
                    if ($sunsetHour < 12) {
                        $sunsetHour += 24;
                    }

                    if ($astroriseHour > 12) {
                        $astroriseHour -= 12;
                    }
                    if ($nautriseHour > 12) {
                        $nautriseHour -= 12;
                    }
                    if ($civilriseHour > 12) {
                        $civilriseHour -= 12;
                    }
                    if ($sunriseHour > 12) {
                        $sunriseHour = 12;
                        $sunriseMinute = 0;
                    }

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5 + ($nautsetHour + $nautsetMinute / 60 - 12) * 15,
                        70 + $runningday * 2,
                        5 + ($astrosetHour + $astrosetMinute / 60 - 12) * 15,
                        $astrocolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5 + ($nautsetHour + $nautsetMinute / 60 - 12) * 15,
                        71 + $runningday * 2,
                        5 + ($astrosetHour + $astrosetMinute / 60 - 12) * 15,
                        $astrocolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5 + ($civilsetHour + $civilsetMinute / 60 - 12) * 15,
                        70 + $runningday * 2,
                        5 + ($nautsetHour + $nautsetMinute / 60 - 12) * 15,
                        $nautcolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5 + ($civilsetHour + $civilsetMinute / 60 - 12) * 15,
                        71 + $runningday * 2,
                        5 + ($nautsetHour + $nautsetMinute / 60 - 12) * 15,
                        $nautcolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5 + ($sunsetHour + $sunsetMinute / 60 - 12) * 15,
                        70 + $runningday * 2,
                        5 + ($civilsetHour + $civilsetMinute / 60 - 12) * 15,
                        $civilcolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5 + ($sunsetHour + $sunsetMinute / 60 - 12) * 15,
                        71 + $runningday * 2,
                        5 + ($civilsetHour + $civilsetMinute / 60 - 12) * 15,
                        $civilcolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5,
                        70 + $runningday * 2,
                        5 + ($sunsetHour + $sunsetMinute / 60 - 12) * 15,
                        $daycolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5,
                        71 + $runningday * 2,
                        5 + ($sunsetHour + $sunsetMinute / 60 - 12) * 15,
                        $daycolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5 + ($nautriseHour + $nautriseMinute / 60 + 12) * 15,
                        70 + $runningday * 2,
                        5 + ($astroriseHour + $astroriseMinute / 60 + 12) * 15,
                        $astrocolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5 + ($nautriseHour + $nautriseMinute / 60 + 12) * 15,
                        71 + $runningday * 2,
                        5 + ($astroriseHour + $astroriseMinute / 60 + 12) * 15,
                        $astrocolor
                    );
                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5 + ($civilriseHour + $civilriseMinute / 60 + 12) * 15,
                        71 + $runningday * 2,
                        5 + ($nautriseHour + $nautriseMinute / 60 + 12) * 15,
                        $nautcolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5 + ($civilriseHour + $civilriseMinute / 60 + 12) * 15,
                        70 + $runningday * 2,
                        5 + ($nautriseHour + $nautriseMinute / 60 + 12) * 15,
                        $nautcolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        5 + ($sunriseHour + $sunriseMinute / 60 + 12) * 15,
                        70 + $runningday * 2,
                        5 + ($civilriseHour + $civilriseMinute / 60 + 12) * 15,
                        $civilcolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        5 + ($sunriseHour + $sunriseMinute / 60 + 12) * 15,
                        71 + $runningday * 2,
                        5 + ($civilriseHour + $civilriseMinute / 60 + 12) * 15,
                        $civilcolor
                    );

                    imageline(
                        $image,
                        70 + $runningday * 2,
                        365,
                        70 + $runningday * 2,
                        5 + ($sunriseHour + $sunriseMinute / 60 + 12) * 15,
                        $daycolor
                    );

                    imageline(
                        $image,
                        71 + $runningday * 2,
                        365,
                        71 + $runningday * 2,
                        5 + ($sunriseHour + $sunriseMinute / 60 + 12) * 15,
                        $daycolor
                    );

                    $runningday++;
                }
                imagestring(
                    $image,
                    2,
                    90 + $i * 61,
                    375,
                    $date->isoFormat('MMM'),
                    $textcolor
                );
            }
            // Date line
            $red = imagecolorallocate($image, 255, 0, 0);
            $datelocation = 2 * ($this->getDate()->dayOfYear);
            imageline($image, $datelocation + 70, 5, $datelocation + 70, 365, $red);

            imageline($image, 802, 365, 802, 355, $axiscolor);

            imagestring($image, 2, 35, 360, '12:00', $textcolor);
            imageline($image, 70, 365, $length, 365, $axiscolor);
            imagestring($image, 2, 35, 315, '09:00', $textcolor);
            imageline($image, 70, 320, $length, 320, $axiscolor);
            imagestring($image, 2, 35, 270, '06:00', $textcolor);
            imageline($image, 70, 275, $length, 275, $axiscolor);
            imagestring($image, 2, 35, 225, '03:00', $textcolor);
            imageline($image, 70, 230, $length, 230, $axiscolor);
            imagestring($image, 2, 35, 180, '00:00', $textcolor);
            imageline($image, 70, 185, $length, 185, $axiscolor);
            imagestring($image, 2, 35, 135, '21:00', $textcolor);
            imageline($image, 70, 140, $length, 140, $axiscolor);
            imagestring($image, 2, 35, 90, '18:00', $textcolor);
            imageline($image, 70, 95, $length, 95, $axiscolor);
            imagestring($image, 2, 35, 45, '15:00', $textcolor);
            imageline($image, 70, 50, $length, 50, $axiscolor);
            imagestring($image, 2, 35, 0, '12:00', $textcolor);
            imageline($image, 70, 5, $length, 5, $axiscolor);

            // Begin capturing the byte stream
            ob_start();

            // generate the byte stream
            imagepng($image);

            // and finally retrieve the byte stream
            $rawImageBytes = ob_get_clean();

            $this->_lengthOfNightChart = "<img src='data:image/jpeg;base64,"
                .base64_encode($rawImageBytes)."' />";
        }

        return $this->_lengthOfNightChart;
    }

    /**
     * Returns true if the three bodies are in a straight line.
     * Chapter 19 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords1   The coordinates of the first object
     * @param EquatorialCoordinates $coords2   The coordinates of the second object
     * @param EquatorialCoordinates $coords3   The coordinates of the thirds object
     * @param float                 $threshold The threshold for the method
     *                                         (default value is 10e-06)
     *
     * @return bool True if the three bodies are in a straight line
     */
    public function isInStraightLine(
        EquatorialCoordinates $coords1,
        EquatorialCoordinates $coords2,
        EquatorialCoordinates $coords3,
        float $threshold = 1e-6
    ): bool {
        return $coords1->isInStraightLine($coords2, $coords2, $threshold);
    }

    /**
     * Returns the deviation from a straight line.
     * Chapter 19 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords1 The coordinates of the first object
     * @param EquatorialCoordinates $coords2 The coordinates of the first object
     * @param EquatorialCoordinates $coords3 The coordinates of the second object
     *
     * @return Coordinate the deviation from the straight line
     */
    public function deviationFromStraightLine(
        EquatorialCoordinates $coords1,
        EquatorialCoordinates $coords2,
        EquatorialCoordinates $coords3
    ): Coordinate {
        return $coords1->deviationFromStraightLine($coords2, $coords3);
    }

    /**
     * Returns the smallest circle containing three celestial bodies.
     * Chapter 20 of Astronomical Algorithms.
     *
     * @param EquatorialCoordinates $coords1 The coordinates of the first object
     * @param EquatorialCoordinates $coords2 The coordinates of the second object
     * @param EquatorialCoordinates $coords3 The coordinates of the third object
     *
     * @return Coordinate the diameter of the smallest circle
     */
    public function smallestCircle(
        EquatorialCoordinates $coords1,
        EquatorialCoordinates $coords2,
        EquatorialCoordinates $coords3
    ): Coordinate {
        return $coords1->smallestCircle($coords2, $coords3);
    }

    /**
     * Returns the apparent place of a star.
     *
     * @param EquatorialCoordinates $coords The coordinates to start with
     *
     * @return EquatorialCoordinates The apparent place for the star
     */
    public function apparentPlace(
        EquatorialCoordinates $coords
    ): EquatorialCoordinates {
        return $coords->apparentPlace($this->getDate(), $this->getNutation());
    }

    /**
     * Returns rhoSinPhi and rhoCosPhi.
     * Needed for the calculation of the parallax.
     *
     * @param float $height The height of the location
     *
     * @return array with rhoSinPhi and rhoCosPhi
     *
     * See Chapter 11 of Astronomical Algorithms
     */
    public function earthsGlobe(): array
    {
        if ($this->_earthsGlobe == null) {
            $this->_earthsGlobe = $this->_coordinates->earthsGlobe($this->_height);
        }

        return $this->_earthsGlobe;
    }
}
