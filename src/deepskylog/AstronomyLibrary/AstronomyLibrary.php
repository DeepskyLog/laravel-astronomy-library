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
        return Time::apparentSiderialTime($this->_date, $this->_coordinates);
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
}
