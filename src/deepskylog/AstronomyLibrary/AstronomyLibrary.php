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
    private $_date;

    /**
     * The constructor.
     *
     * @param Carbon $carbonDate The date
     */
    public function __construct(Carbon $carbonDate)
    {
        $this->_date = $carbonDate;
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
    }

    /**
     * Returns the julian day of the date.
     *
     * @return float The julian day
     */
    public function getJd(): float
    {
        return Time::getJd($this->_date);
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
     * Returns dynamical dateof the date.
     *
     * @return Carbon The dynamical time
     */
    public function getDynamicalTime(): Carbon
    {
        return Time::dynamicalTime($this->_date);
    }
}
