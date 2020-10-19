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

use Carbon\Carbon;
use DateTimeZone;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;
use deepskylog\AstronomyLibrary\Time;

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
    protected $appPath = __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php';

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
        $coords = new GeographicalCoordinates(12.345, 32.1);
        $astrolib = new AstronomyLibrary($date, $coords);

        $this->assertEquals($date, $astrolib->getDate());
        $this->assertEquals(2440870.5, $astrolib->getJd());

        $now = Carbon::now(new DateTimeZone('Europe/Brussels'));
        $astrolib = new AstronomyLibrary($now, $coords);

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
        $coords = new GeographicalCoordinates(12.345, 32.1);
        $astrolib = new AstronomyLibrary($date, $coords);

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

    /**
     * Test getting delta T.
     *
     * @return None
     */
    public function testGetDeltaT()
    {
        $this->assertEquals(
            7359,
            Time::deltaT(Carbon::create(333, 1, 1, 21, 36, 0, 'UTC'))
        );
    }

    /**
     * Test getting dynamical time.
     *
     * @return None
     */
    public function testGetDynamicalTimeStatic()
    {
        $date = Carbon::create(333, 2, 6, 6, 0, 0, 'UTC');
        $this->assertEquals(
            Time::dynamicalTime($date),
            Carbon::create(333, 2, 6, 8, 2, 38, 'UTC')
        );
    }

    /**
     * Test getting dynamical time.
     *
     * @return None
     */
    public function testGetDynamicalTime()
    {
        $date = Carbon::create(333, 2, 6, 6, 0, 0, 'UTC');
        $coords = new GeographicalCoordinates(12.345, 32.1);
        $astronomylib = new AstronomyLibrary($date, $coords);
        $this->assertEquals(
            $astronomylib->getDynamicalTime(),
            Carbon::create(333, 2, 6, 8, 2, 38, 'UTC')
        );
    }

    /**
     * Test getting mean siderial time.
     *
     * @return None
     */
    public function testGetMeanSiderialTimeStatic()
    {
        $date = Carbon::create(1987, 4, 10, 19, 21, 0, 'UTC');
        $coords = new GeographicalCoordinates(0.0, 32.1);

        $this->assertEquals(
            Time::meanSiderialTime($date, $coords),
            Carbon::create(1987, 4, 10, 8, 34, 57.089579, 'UTC')
        );

        $date = Carbon::create(1987, 4, 10, 0, 0, 0, 'UTC');

        $this->assertEquals(
            Time::meanSiderialTime($date, $coords),
            Carbon::create(1987, 4, 10, 13, 10, 46.366822, 'UTC')
        );

        $coords->setLongitude(13.41);
        $date = Carbon::create(2020, 4, 28, 13, 47, 8, 'Europe/Brussels');

        $this->assertEquals(
            Time::apparentSiderialTime($date, $coords),
            Carbon::create(2020, 4, 28, 3, 8, 24.210309, 'UTC')
        );
    }

    /**
     * Test getting apparent siderial time.
     *
     * @return None
     */
    public function testGetApparentSiderialTimeStatic()
    {
        $date = Carbon::create(1987, 4, 10, 0, 0, 0, 'UTC');
        $coords = new GeographicalCoordinates(0.0, 32.1);

        $this->assertEquals(
            Time::apparentSiderialTime($date, $coords),
            Carbon::create(1987, 4, 10, 13, 10, 46.135138, 'UTC')
        );
    }

    /**
     * Test getting nutation.
     *
     * @return None
     */
    public function testGetNutation()
    {
        $date = Carbon::create(1987, 4, 10, 0, 0, 0, 'UTC');

        $jd = Time::getJd($date);

        $nutat = Time::nutation($jd);

        $this->assertEqualsWithDelta(
            -3.788,
            $nutat[0],
            0.001
        );

        $this->assertEqualsWithDelta(
            9.443,
            $nutat[1],
            0.001
        );

        $this->assertEqualsWithDelta(
            23.44094629,
            $nutat[2],
            0.00000001
        );

        $this->assertEqualsWithDelta(
            23.44356921,
            $nutat[3],
            0.00000001
        );
    }

    /**
     * Test getting start of seasons.
     *
     * @return None
     */
    public function testGetSeasons()
    {
        $date = Carbon::create(1996, 4, 1, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(1996, 3, 20, 8, 4, 14, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(1996, 6, 21, 2, 24, 38, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(1996, 9, 22, 18, 1, 23, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(1996, 12, 21, 14, 7, 18, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(1997, 4, 12, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(1997, 3, 20, 13, 56, 9, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(1997, 6, 21, 8, 21, 9, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(1997, 9, 22, 23, 56, 36, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(1997, 12, 21, 20, 8, 19, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(1998, 3, 24, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(1998, 3, 20, 19, 55, 30, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(1998, 6, 21, 14, 3, 30, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(1998, 9, 23, 5, 38, 36, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(1998, 12, 22, 1, 57, 33, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(1999, 5, 4, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(1999, 3, 21, 1, 46, 59, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(1999, 6, 21, 19, 50, 13, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(1999, 9, 23, 11, 32, 38, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(1999, 12, 22, 7, 45, 18, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(2000, 6, 5, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(2000, 3, 20, 7, 36, 28, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(2000, 6, 21, 1, 48, 47, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(2000, 9, 22, 17, 28, 54, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(2000, 12, 21, 13, 38, 44, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(2001, 7, 6, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(2001, 3, 20, 13, 32, 4, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(2001, 6, 21, 7, 38, 49, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(2001, 9, 22, 23, 5, 35, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(2001, 12, 21, 19, 22, 46, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(2002, 8, 7, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(2002, 3, 20, 19, 17, 20, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(2002, 6, 21, 13, 25, 57, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(2002, 9, 23, 4, 56, 38, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(2002, 12, 22, 1, 15, 50, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(2003, 9, 8, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(2003, 3, 21, 1, 1, 30, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(2003, 6, 21, 19, 11, 46, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(2003, 9, 23, 10, 48, 5, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(2003, 12, 22, 7, 5, 3, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(2004, 10, 9, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(2004, 3, 20, 6, 49, 51, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(2004, 6, 21, 0, 57, 54, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(2004, 9, 22, 16, 31, 4, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(2004, 12, 21, 12, 42, 50, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(2005, 11, 10, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(2005, 3, 20, 12, 34, 48, 'UTC'), Time::getSpring($date));
        $this->assertEquals(Carbon::create(2005, 6, 21, 6, 47, 16, 'UTC'), Time::getSummer($date));
        $this->assertEquals(Carbon::create(2005, 9, 22, 22, 23, 48, 'UTC'), Time::getAutumn($date));
        $this->assertEquals(Carbon::create(2005, 12, 21, 18, 36, 29, 'UTC'), Time::getWinter($date));

        $date = Carbon::create(1962, 11, 10, 0, 0, 0, 'UTC');
        $this->assertEquals(Carbon::create(1962, 6, 21, 21, 25, 7, 'UTC'), Time::getSummer($date));
    }
}
