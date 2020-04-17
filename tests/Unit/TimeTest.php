<?php
/**
 * Tests for the time methods.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use DateTimeZone;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the time methods.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TimeTest extends BaseTestCase
{
    /**
     * Base app path.
     *
     * @var string
     */
    protected $appPath = __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';

    /**
     * Setup the test environment.
     *
     * @return None
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test converting from Carbon time to julian day.
     *
     * @return None
     */
    public function testConvertToJd()
    {
        $date = Carbon::create(1970, 10, 11, 0, 0, 0, 'UTC');
        $astrolib = new AstronomyLibrary($date);

        $this->assertEquals($date, $astrolib->getDate());
        $this->assertEquals(2440870.5, $astrolib->getJd());

        $now = Carbon::now(new DateTimeZone('Europe/Brussels'));
        $astrolib = new AstronomyLibrary($now);

        $this->assertEquals($now, $astrolib->getDate());

        $date = Carbon::create(1957, 10, 4, 19, 26, 24, 'UTC');
        $astrolib->setDate($date);
        $this->assertEquals($date, $astrolib->getDate());
        $this->assertEquals(2436116.31, $astrolib->getJd());
    }

    /**
     * Test converting from julian day to Carbon time.
     *
     * @return None
     */
    public function testUpdateJd()
    {
        $date = Carbon::create(1970, 10, 11, 0, 0, 0, 'UTC');
        $astrolib = new AstronomyLibrary($date);

        $this->assertEquals($date, $astrolib->getDate());
        $this->assertEquals(2440870.5, $astrolib->getJd());

        $astrolib->setJd(1842713.0);
        $this->assertEquals(
            Carbon::create(333, 1, 27, 12, 0, 0, 'UTC'),
            $astrolib->getDate()
        );
        $this->assertEquals(1842713.0, $astrolib->getJd());
    }

    /**
     * Test converting from Carbon time to julian day using static method.
     *
     * @return None
     */
    public function testStaticConvertToJd()
    {
        // Month, Day, Year: 10, 11, 1970
        $date = Carbon::create(1970, 10, 11, 0, 0, 0, 'UTC');
        $this->assertEquals(2440870.5, Time::getJd($date));

        // Launch of Sputnik 1: 1957, October 4.81 -> JD = 2436116.31
        $date = Carbon::create(1957, 10, 4, 19, 26, 24, 'UTC');
        $this->assertEquals(2436116.31, Time::getJd($date));

        // 333, January 27, 12:00 -> 1842713.0
        $date = Carbon::create(333, 1, 27, 12, 0, 0, 'UTC');
        $this->assertEquals(1842713.0, Time::getJd($date));

        // 2000 Jan. 1.5 -> 2451545.0
        $date = Carbon::create(2000, 1, 1, 12, 0, 0, 'UTC');
        $this->assertEquals(2451545.0, Time::getJd($date));

        // 1987 Jan. 27.0 -> 2446822.5
        $date = Carbon::create(1987, 1, 27, 0, 0, 0, 'UTC');
        $this->assertEquals(2446822.5, Time::getJd($date));

        // 1987 June 19.5 -> 2446966.0
        $date = Carbon::create(1987, 6, 19, 12, 0, 0, 'UTC');
        $this->assertEquals(2446966.0, Time::getJd($date));

        // 1988 Jan 27.0 -> 2447187.5
        $date = Carbon::create(1988, 1, 27, 0, 0, 0, 'UTC');
        $this->assertEquals(2447187.5, Time::getJd($date));

        // 1988 June 19.5 -> 2447332.0
        $date = Carbon::create(1988, 6, 19, 12, 0, 0, 'UTC');
        $this->assertEquals(2447332.0, Time::getJd($date));

        // 1900 Jan 1.0 -> 2415020.5
        $date = Carbon::create(1900, 1, 1, 0, 0, 0, 'UTC');
        $this->assertEquals(2415020.5, Time::getJd($date));

        // 1600 Jan 1.0 -> 2305447.5
        $date = Carbon::create(1600, 1, 1, 0, 0, 0, 'UTC');
        $this->assertEquals(2305447.5, Time::getJd($date));

        // 1600 Dec 31.0 -> 2305812.5
        $date = Carbon::create(1600, 12, 31, 0, 0, 0, 'UTC');
        $this->assertEquals(2305812.5, Time::getJd($date));

        // 837 Apr. 10.3 -> 2026871.8
        $date = Carbon::create(837, 4, 10, 7, 12, 0, 'UTC');
        $this->assertEquals(2026871.8, Time::getJd($date));

        // -1000 Jul. 12.5 -> 1356001.0
        $date = Carbon::create(-1000, 7, 12, 12, 0, 0, 'UTC');
        $this->assertEquals(1356001.0, Time::getJd($date));

        // -1000 Feb 29.0 -> 1355867.5
        $date = Carbon::create(-1000, 2, 29, 0, 0, 0, 'UTC');
        $this->assertEquals(1355867.5, Time::getJd($date));

        // -1001 Aug 17.9 -> 1355671.4
        $date = Carbon::create(-1001, 8, 17, 21, 36, 0, 'UTC');
        $this->assertEquals(1355671.4, Time::getJd($date));

        // -4712 Jan 1.5 -> 0.0
        $date = Carbon::create(-4712, 1, 1, 12, 0, 0, 'UTC');
        $this->assertEquals(0.0, Time::getJd($date));
    }

    /**
     * Test converting from julian day to Carbon time using static method.
     *
     * @return None
     */
    public function testStaticConvertFromJd()
    {
        $this->assertEquals(
            Time::fromJd(2440870.5),
            Carbon::create(1970, 10, 11, 0, 0, 0, 'UTC')
        );

        // 2436116.31 -> 4.81 October 1957
        $this->assertEquals(
            Time::fromJd(2436116.31),
            Carbon::create(1957, 10, 4, 19, 26, 24, 'UTC')
        );

        // 1842713.0 -> 333 Jan 27.5
        $this->assertEquals(
            Time::fromJd(1842713.0),
            Carbon::create(333, 1, 27, 12, 0, 0, 'UTC')
        );

        // 2000 Jan. 1.5 -> 2451545.0
        $this->assertEquals(
            Time::fromJd(2451545.0),
            Carbon::create(2000, 1, 1, 12, 0, 0, 'UTC')
        );

        // 1987 Jan. 27.0 -> 2446822.5
        $this->assertEquals(
            Time::fromJd(2446822.5),
            Carbon::create(1987, 1, 27, 0, 0, 0, 'UTC')
        );

        // 1987 June 19.5 -> 2446966.0
        $this->assertEquals(
            Time::fromJd(2446966.0),
            Carbon::create(1987, 6, 19, 12, 0, 0, 'UTC')
        );

        // 1988 Jan 27.0 -> 2447187.5
        $this->assertEquals(
            Time::fromJd(2447187.5),
            Carbon::create(1988, 1, 27, 0, 0, 0, 'UTC')
        );

        // 1988 June 19.5 -> 2447332.0
        $this->assertEquals(
            Time::fromJd(2447332.0),
            Carbon::create(1988, 6, 19, 12, 0, 0, 'UTC')
        );

        // 1900 Jan 1.0 -> 2415020.5
        $this->assertEquals(
            Time::fromJd(2415020.5),
            Carbon::create(1900, 1, 1, 0, 0, 0, 'UTC')
        );

        // 1600 Jan 1.0 -> 2305447.5
        $this->assertEquals(
            Time::fromJd(2305447.5),
            Carbon::create(1600, 1, 1, 0, 0, 0, 'UTC')
        );

        // 1600 Dec 31.0 -> 2305812.5
        $this->assertEquals(
            Time::fromJd(2305812.5),
            Carbon::create(1600, 12, 31, 0, 0, 0, 'UTC')
        );

        // 837 Apr. 10.3 -> 2026871.8
        $this->assertEquals(
            Time::fromJd(2026871.8),
            Carbon::create(837, 4, 10, 7, 12, 0, 'UTC')
        );

        // -1000 Jul. 12.5 -> 1356001.0
        $this->assertEquals(
            Time::fromJd(1356001.0),
            Carbon::create(-1000, 7, 12, 12, 0, 0, 'UTC')
        );

        // -1000 Feb 29.0 -> 1355867.5
        $this->assertEquals(
            Time::fromJd(1355867.5),
            Carbon::create(-1000, 2, 29, 0, 0, 0, 'UTC')
        );

        // -1001 Aug 17.9 -> 1355671.4
        $this->assertEquals(
            Time::fromJd(1355671.4),
            Carbon::create(-1001, 8, 17, 21, 35, 59, 'UTC')
        );

        // -4712 Jan 1.5 -> 0.0
        $this->assertEquals(
            Time::fromJd(0.0),
            Carbon::create(-4712, 1, 1, 12, 0, 0, 'UTC')
        );

        // 1507900.13 -> -584 May 28.63
        $this->assertEquals(
            Time::fromJd(1507900.13),
            Carbon::create(-584, 5, 28, 15, 7, 11, 'UTC')
        );
    }

    /**
     * Test exceptions for wrong dates.
     *
     * @return None
     */
    public function testWrongDateGregorianJulian()
    {
        $date = Carbon::create(1582, 10, 10, 21, 36, 0, 'UTC');
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        Time::getJd($date);

        $date = Carbon::create(-4725, 10, 10, 21, 36, 0, 'UTC');
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        Time::getJd($date);
    }

    /**
     * Test exceptions for wrong dates.
     *
     * @return None
     */
    public function testWrongDateTooEarly()
    {
        $date = Carbon::create(-4725, 10, 10, 21, 36, 0, 'UTC');
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        Time::getJd($date);
    }

    /**
     * Test exceptions for wrong dates.
     *
     * @return None
     */
    public function testNegativeJd()
    {
        $jd = -1234.4321;
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        Time::fromJd($jd);
    }
}
