<?php
/**
 * The main AstronomyLibrary class.
 *
 * PHP Version 7
 *
 * @category AstronomyLibrary
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GalacticCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\HorizontalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * The main AstronomyLibrary class.
 *
 * PHP Version 7
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

    /**
     * The constructor.
     *
     * @param Carbon                  $carbonDate  The date
     * @param GeographicalCoordinates $coordinates The geographical coordinates
     */
    public function __construct(
        Carbon $carbonDate,
        GeographicalCoordinates $coordinates
    ) {
        $this->_date = $carbonDate;
        $this->_coordinates = $coordinates;
        $this->_jd = Time::getJd($this->_date);
        $this->_nutation = Time::nutation($this->getJd());
        $this->_siderialTime = Time::apparentSiderialTime(
            $this->_date,
            $this->_coordinates
        );
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
        $this->_jd = Time::getJd($this->_date);
        $this->_nutation = Time::nutation($this->getJd());
        $this->_siderialTime = Time::apparentSiderialTime(
            $this->_date,
            $this->_coordinates
        );
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
    }

    /**
     * Returns delta t of the date.
     *
     * @return float delta t
     */
    public function getDeltaT(): float
    {
        return Time::deltaT($this->_date);
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
}
