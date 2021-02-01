<?php
/**
 * Tests for the target classes.
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
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Targets\Jupiter;
use deepskylog\AstronomyLibrary\Targets\Mars;
use deepskylog\AstronomyLibrary\Targets\Mercury;
use deepskylog\AstronomyLibrary\Targets\Moon;
use deepskylog\AstronomyLibrary\Targets\Neptune;
use deepskylog\AstronomyLibrary\Targets\Parabolic;
use deepskylog\AstronomyLibrary\Targets\Planet;
use deepskylog\AstronomyLibrary\Targets\Saturn;
use deepskylog\AstronomyLibrary\Targets\Target;
use deepskylog\AstronomyLibrary\Targets\Uranus;
use deepskylog\AstronomyLibrary\Targets\Venus;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;
use deepskylog\AstronomyLibrary\Time;

/**
 * Tests for the target classes.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class TargetTest extends BaseTestCase
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
     * Test moon class.
     *
     * @return None
     */
    public function testMoonClass()
    {
        $moon = new Moon();
        $this->assertEqualsWithDelta(0.125, $moon->getH0(), 0.001);
    }

    /**
     * Test planet class.
     *
     * @return None
     */
    public function testPlanetClass()
    {
        $planet = new Planet();
        $this->assertEqualsWithDelta(-0.5667, $planet->getH0(), 0.0001);
    }

    /**
     * Test base target class.
     *
     * @return None
     */
    public function testTargetClass()
    {
        $target = new Target();
        $this->assertEqualsWithDelta(-0.5667, $target->getH0(), 0.0001);
    }

    /**
     * Test rising, transit and setting of a moving object.
     *
     * @return None
     */
    public function testRisingTransitSettingVenus()
    {
        $date = Carbon::create(1988, 3, 20, 12);
        $geo_coords = new GeographicalCoordinates(-71.0833, 42.3333);
        $equaToday = new EquatorialCoordinates(2.782086, 18.44092);
        $equaTomorrow = new EquatorialCoordinates(2.852136, 18.82742);
        $equaYesterday = new EquatorialCoordinates(2.712014, 18.04761);

        $target = new Planet();
        $target->setEquatorialCoordinatesYesterday($equaYesterday);
        $target->setEquatorialCoordinatesToday($equaToday);
        $target->setEquatorialCoordinatesTomorrow($equaTomorrow);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 56.0);

        $this->assertEquals(
            Carbon::create(1988, 3, 20, 19, 40, 30, 'UTC'),
            $target->getTransit()
        );

        $this->assertEquals(
            Carbon::create(1988, 3, 20, 12, 25, 25, 'UTC'),
            $target->getRising()
        );

        $this->assertEquals(
            Carbon::create(1988, 3, 20, 2, 54, 39, 'UTC'),
            $target->getSetting()
        );

        $coords = new Coordinate(66.42512094097216, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(24.957398499998945, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(1988, 3, 21, 0, 31, 0),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a moving object.
     *
     * @return None
     */
    public function testRisingTransitSettingVenus2()
    {
        $date = Carbon::create(2020, 5, 18, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equaToday = new EquatorialCoordinates(5.33815, 26.8638);
        $equaTomorrow = new EquatorialCoordinates(5.3236, 26.7175);
        $equaYesterday = new EquatorialCoordinates(5.3498, 26.9984);

        $target = new Planet();
        $target->setEquatorialCoordinatesYesterday($equaYesterday);
        $target->setEquatorialCoordinatesToday($equaToday);
        $target->setEquatorialCoordinatesTomorrow($equaTomorrow);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 56.0);

        $this->assertEquals(
            Carbon::create(2020, 5, 18, 13, 13, 38, 'UTC'),
            $target->getTransit()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 18, 4, 36, 37, 'UTC'),
            $target->getRising()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 18, 21, 49, 44, 'UTC'),
            $target->getSetting()
        );

        $coords = new Coordinate(65.94678370157925, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(4.700095764337803, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 18, 21, 05, 33),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a fixed object that does not rise.
     *
     * @return None
     */
    public function testRisingTransitSettingNoRise()
    {
        $date = Carbon::create(1988, 3, 20, 12);
        $geo_coords = new GeographicalCoordinates(-71.0833, 42.3333);
        $equa = new EquatorialCoordinates(2.852136, -78.82742);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 56.0);

        $this->assertEquals(
            Carbon::create(1988, 3, 20, 19, 44, 29, 'UTC'),
            $target->getTransit()
        );

        $this->assertNull(
            $target->getRising()
        );

        $this->assertNull(
            $target->getSetting()
        );

        $coords = new Coordinate(-31.161680183736763, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(-36.73647702438957, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(1988, 3, 20, 23, 57, 29),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a fixed object that is circumpolar.
     *
     * @return None
     */
    public function testRisingTransitSettingCircumpolar()
    {
        $date = Carbon::create(1988, 3, 20, 12);
        $geo_coords = new GeographicalCoordinates(-71.0833, 42.3333);
        $equa = new EquatorialCoordinates(2.852136, 85.82742);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 56.0);

        $this->assertEquals(
            Carbon::create(1988, 3, 20, 19, 44, 29, 'UTC'),
            $target->getTransit()
        );

        $this->assertNull(
            $target->getRising()
        );

        $this->assertNull(
            $target->getSetting()
        );

        $coords = new Coordinate(46.505431730243515, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(43.518803366063935, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(1988, 3, 21, 0, 31, 0),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a fixed object.
     *
     * @return None
     */
    public function testRisingTransitSettingBelgium()
    {
        $date = Carbon::create(2020, 5, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa = new EquatorialCoordinates(13.703055555555556, 28.37555556);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 69.36);

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 21, 57, 53, 'UTC'),
            $target->getTransit()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 13, 6, 15, 'UTC'),
            $target->getRising()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 6, 49, 31, 'UTC'),
            $target->getSetting()
        );

        $coords = new Coordinate(67.53302740914324, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(67.37147710073731, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 22, 9, 38),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a fixed object that has transit
     * during the night.
     *
     * @return None
     */
    public function testRisingTransitSetting2()
    {
        $date = Carbon::create(2020, 5, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa = new EquatorialCoordinates(16.695, 36.460278);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 69.36);

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 0, 57, 24, 'UTC'),
            $target->getTransit()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 14, 25, 50, 'UTC'),
            $target->getRising()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 11, 28, 58, 'UTC'),
            $target->getSetting()
        );

        $coords = new Coordinate(75.62805042447934, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(75.62805042447934, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 0, 57, 24),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a fixed object that has transit
     * during the night.
     *
     * @return None
     */
    public function testRisingTransitSettingTimezone()
    {
        $date = Carbon::create(2020, 5, 13, 12);
        $date->timezone('Europe/Brussels');
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa = new EquatorialCoordinates(16.695, 36.460278);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 69.36);

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 2, 57, 24, 'Europe/Brussels'),
            $target->getTransit()->timezone('Europe/Brussels')
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 16, 25, 50, 'Europe/Brussels'),
            $target->getRising()->timezone('Europe/Brussels')
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 13, 28, 58, 'Europe/Brussels'),
            $target->getSetting()->timezone('Europe/Brussels')
        );

        $coords = new Coordinate(75.62805042447934, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(75.62805042447934, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(2020, 5, 13, 2, 57, 24, 'Europe/Brussels'),
            $target->getBestTimeToObserve()->timezone('Europe/Brussels')
        );
    }

    /**
     * Test rising, transit and setting of a fixed object, when there is no
     * astronomical darkness.
     *
     * @return None
     */
    public function testRisingTransitSettingNoAstronomicalDarkness()
    {
        $date = Carbon::create(2020, 6, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa = new EquatorialCoordinates(16.695, 36.460278);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);

        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 69.36);

        $this->assertEquals(
            Carbon::create(2020, 6, 13, 22, 55, 11, 'UTC'),
            $target->getTransit()
        );

        $this->assertEquals(
            Carbon::create(2020, 6, 13, 12, 23, 37, 'UTC'),
            $target->getRising()
        );

        $this->assertEquals(
            Carbon::create(2020, 6, 13, 9, 26, 45, 'UTC'),
            $target->getSetting()
        );

        $coords = new Coordinate(75.61226348139695, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(75.61226348139695, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertEquals(
            Carbon::create(2020, 6, 13, 22, 55, 11),
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test rising, transit and setting of a fixed object, when there is no
     * astronomical darkness.
     *
     * @return None
     */
    public function testRisingTransitSettingNoAstronomicalDarkness2()
    {
        $date = Carbon::create(2020, 6, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 80.83220);
        $equa = new EquatorialCoordinates(16.695, 36.460278);

        $target = new Target();
        $target->setEquatorialCoordinates($equa);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
        $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, 69.36);

        $this->assertEquals(
            Carbon::create(2020, 6, 13, 22, 55, 11, 'UTC'),
            $target->getTransit()
        );

        $this->assertNull($target->getRising());

        $this->assertNull($target->getSetting());

        $coords = new Coordinate(45.62666125733051, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeight()
        );

        $coords = new Coordinate(45.62666125733051, -90.0, 90.0);
        $this->assertEquals(
            $coords,
            $target->getMaxHeightAtNight()
        );

        $this->assertNull(
            $target->getBestTimeToObserve()
        );
    }

    /**
     * Test the Equation of Kepler.
     *
     * @return None
     */
    public function testEquationOfKepler()
    {
        $target = new Target();
        $this->assertEqualsWithDelta(5.554589, $target->eccentricAnomaly(0.1, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(6.246908, $target->eccentricAnomaly(0.2, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(7.134960, $target->eccentricAnomaly(0.3, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(8.313903, $target->eccentricAnomaly(0.4, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(9.950062, $target->eccentricAnomaly(0.5, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(12.356653, $target->eccentricAnomaly(0.6, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(16.167988, $target->eccentricAnomaly(0.7, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(22.656576, $target->eccentricAnomaly(0.8, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(33.344444, $target->eccentricAnomaly(0.9, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(45.361021, $target->eccentricAnomaly(0.99, 5, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(24.725813, $target->eccentricAnomaly(0.99, 1, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(32.361002, $target->eccentricAnomaly(0.99, 2, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(89.722155, $target->eccentricAnomaly(0.99, 33, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(49.569623, $target->eccentricAnomaly(0.999, 6, 0.000001), 0.000001);
        $this->assertEqualsWithDelta(52.270260, $target->eccentricAnomaly(0.999, 7, 0.000001), 0.000001);
    }

    /**
     * Test calculating the mean orbital parameters of Mercury.
     */
    public function testMeanOrbitalParametersMercury()
    {
        $date = Carbon::create(2065, 6, 24, 0);
        $mercury = new Mercury();
        $parameters = $mercury->calculateMeanOrbitalElements($date);
        $this->assertEqualsWithDelta(203.494701, $parameters[0], 0.000001);
        $this->assertEqualsWithDelta(0.387098310, $parameters[1], 0.000001);
        $this->assertEqualsWithDelta(0.20564510, $parameters[2], 0.000001);
        $this->assertEqualsWithDelta(7.006171, $parameters[3], 0.000001);
        $this->assertEqualsWithDelta(49.107650, $parameters[4], 0.000001);
        $this->assertEqualsWithDelta(78.475382, $parameters[5], 0.000001);
        $this->assertEqualsWithDelta(125.019319, $parameters[6], 0.000001);
    }

    /**
     * Test calculating the heliocentric coordinates of Venus.
     */
    public function testHeliocentricCoordinatesVenus()
    {
        $date = Carbon::create(1992, 12, 20, 0);
        $venus = new Venus();
        $coords = $venus->calculateHeliocentricCoordinates($date);
        $this->assertEqualsWithDelta(26.11412, $coords[0], 0.00001);
        $this->assertEqualsWithDelta(-2.62060, $coords[1], 0.00001);
        $this->assertEqualsWithDelta(0.724602, $coords[2], 0.000001);
    }

    /**
     * Test the apparent position of Venus on 1992 December 20 at 0:00.
     */
    public function testApparentPositionOfVenus()
    {
        $date = Carbon::create(1992, 12, 20, 0, 0, 0, 'UTC');
        $venus = new Venus();

        $nutation = Time::nutation(Time::getJd($date));
        $venus->calculateEquatorialCoordinates($date, $nutation[3]);
        $coordinates = $venus->getEquatorialCoordinates();

        $this->assertEqualsWithDelta(21.078181, $coordinates->getRA()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(-18.88802, $coordinates->getDeclination()->getCoordinate(), 0.00001);
    }

    /**
     * Test the apparent position of comet Encke on 1990 October 6.
     */
    public function testEquatorialCoordinatesOfEncke()
    {
        $date = Carbon::create(1990, 10, 6, 0, 0, 0, 'UTC');
        $encke = new Elliptic();
        $peridate = Carbon::create(1990, 10, 28, 13, 4, 50, 'UTC');
        $encke->setOrbitalElements(2.2091404, 0.8502196, 11.94524, 186.23352, 334.75006, $peridate);

        $nutation = Time::nutation(Time::getJd($date));
        $encke->calculateEquatorialCoordinates($date, $nutation[3]);
        $coordinates = $encke->getEquatorialCoordinates();

        $this->assertEqualsWithDelta(10.56228318, $coordinates->getRA()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(19.18870874, $coordinates->getDeclination()->getCoordinate(), 0.00001);
    }

    /**
     * Test the apparent position of comet Stonehouse (C/1998 H1) on 1998 August 5.
     */
    public function testEquatorialCoordinatesOfStonehouse()
    {
        $date = Carbon::create(1998, 8, 5, 0, 0, 0, 'UTC');
        $stonehouse = new Parabolic();
        $peridate = Carbon::create(1998, 4, 14, 10, 27, 33, 'UTC');
        $stonehouse->setOrbitalElements(1.487469, 104.69219, 1.32431, 222.10887, $peridate);

        $nutation = Time::nutation(Time::getJd($date));
        $stonehouse->calculateEquatorialCoordinates($date, $nutation[3]);
        $coordinates = $stonehouse->getEquatorialCoordinates();

        $this->assertEqualsWithDelta(12.523385, $coordinates->getRA()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(50.7636309, $coordinates->getDeclination()->getCoordinate(), 0.00001);
    }

    /**
     * Test the date of the inferior conjunction of Mercury.
     */
    public function testMercuryInferiorConjunction()
    {
        $date = Carbon::create(1993, 10, 1, 0, 0, 0, 'UTC');
        $mercury = new Mercury();
        $inf = $mercury->inferior_conjunction($date);
        $this->assertEquals($inf->year, 1993);
        $this->assertEquals($inf->month, 11);
        $this->assertEquals($inf->day, 6);
        $this->assertEquals($inf->hour, 3);

        $date = Carbon::create(1631, 10, 1, 0, 0, 0, 'UTC');
        $mercury = new Mercury();
        $inf = $mercury->inferior_conjunction($date);
        $this->assertEquals($inf->year, 1631);
        $this->assertEquals($inf->month, 11);
        $this->assertEquals($inf->day, 7);
        $this->assertEquals($inf->hour, 7);
    }

    /**
     * Test the date of the inferior conjunction of venus.
     */
    public function testVenusInferiorConjunction()
    {
        $date = Carbon::create(1882, 10, 1, 0, 0, 0, 'UTC');
        $venus = new Venus();
        $inf = $venus->inferior_conjunction($date);
        $this->assertEquals($inf->year, 1882);
        $this->assertEquals($inf->month, 12);
        $this->assertEquals($inf->day, 6);
        $this->assertEquals($inf->hour, 16);
    }

    /**
     * Test the date of the opposition of Mars.
     */
    public function testMarsOpposition()
    {
        $date = Carbon::create(2729, 1, 1, 0, 0, 0, 'UTC');
        $mars = new Mars();
        $opposition = $mars->opposition($date);
        $this->assertEquals($opposition->year, 2729);
        $this->assertEquals($opposition->month, 9);
        $this->assertEquals($opposition->day, 9);
        $this->assertEquals($opposition->hour, 3);
    }

    /**
     * Test the date of the opposition of Jupiter.
     */
    public function testJupiterOpposition()
    {
        $date = Carbon::create(-6, 5, 1, 0, 0, 0, 'UTC');
        $jupiter = new Jupiter();
        $opposition = $jupiter->opposition($date);
        $this->assertEquals($opposition->year, -6);
        $this->assertEquals($opposition->month, 9);
        $this->assertEquals($opposition->day, 15);
        $this->assertEquals($opposition->hour, 6);
    }

    /**
     * Test the date of the opposition of Saturn.
     */
    public function testSaturnOpposition()
    {
        $date = Carbon::create(-6, 5, 1, 0, 0, 0, 'UTC');
        $saturn = new Saturn();
        $opposition = $saturn->opposition($date);
        $this->assertEquals($opposition->year, -6);
        $this->assertEquals($opposition->month, 9);
        $this->assertEquals($opposition->day, 14);
        $this->assertEquals($opposition->hour, 9);

        $date = Carbon::create(2125, 5, 1, 0, 0, 0, 'UTC');
        $saturn = new Saturn();
        $opposition = $saturn->conjunction($date);
        $this->assertEquals($opposition->year, 2125);
        $this->assertEquals($opposition->month, 8);
        $this->assertEquals($opposition->day, 26);
        $this->assertEquals($opposition->hour, 7);
    }

    /**
     * Test the date of the opposition of Uranus.
     */
    public function testUranusOpposition()
    {
        $date = Carbon::create(1780, 10, 1, 0, 0, 0, 'UTC');
        $uranus = new Uranus();
        $opposition = $uranus->opposition($date);
        $this->assertEquals($opposition->year, 1780);
        $this->assertEquals($opposition->month, 12);
        $this->assertEquals($opposition->day, 17);
        $this->assertEquals($opposition->hour, 14);
    }

    /**
     * Test the date of the opposition of Neptune.
     */
    public function testNeptuneOpposition()
    {
        $date = Carbon::create(1846, 5, 1, 0, 0, 0, 'UTC');
        $neptune = new Neptune();
        $opposition = $neptune->opposition($date);
        $this->assertEquals($opposition->year, 1846);
        $this->assertEquals($opposition->month, 8);
        $this->assertEquals($opposition->day, 20);
        $this->assertEquals($opposition->hour, 3);
    }

    /**
     * Test the date of the greatest western elongation of Merury.
     */
    public function testMercuryWesternElongation()
    {
        $date = Carbon::create(1993, 11, 1, 0, 0, 0, 'UTC');
        $mercury = new Mercury();
        $elongation = $mercury->greatest_western_elongation($date);
        $this->assertEquals($elongation->year, 1993);
        $this->assertEquals($elongation->month, 11);
        $this->assertEquals($elongation->day, 22);
        $this->assertEquals($elongation->hour, 15);
    }

    /**
     * Test the perihelion date for Venus nearest to 1978 October 15.
     */
    public function testPerihelionDateVenus()
    {
        $date = Carbon::create(1978, 10, 15, 0, 0, 0, 'UTC');
        $venus = new Venus();
        $perihelion = $venus->perihelionDate($date);
        $this->assertEquals($perihelion->year, 1978);
        $this->assertEquals($perihelion->month, 12);
        $this->assertEquals($perihelion->day, 31);
        $this->assertEquals($perihelion->hour, 4);
    }

    /**
     * Test the aphelion date for Mars in 2032.
     */
    public function testAphelionDateMars()
    {
        $date = Carbon::create(2032, 1, 1, 0, 0, 0, 'UTC');
        $mars = new Mars();
        $aphelion = $mars->aphelionDate($date);
        $this->assertEquals($aphelion->year, 2032);
        $this->assertEquals($aphelion->month, 10);
        $this->assertEquals($aphelion->day, 24);
        $this->assertEquals($aphelion->hour, 22);
    }

    /**
     * Test the passage through the nodes.
     */
    public function testPassageThroughNodes()
    {
        // Elliptic
        $halley = new Elliptic();
        $peridate = Carbon::create(1986, 2, 9, 11, 0, 50, 'UTC');
        $halley->setOrbitalElements(17.9400782, 0.96727426, 162.0, 111.84644, 0.0, $peridate);

        // Ascending node
        $node = $halley->ascendingNode();

        $this->assertEquals(1985, $node->year);
        $this->assertEquals(11, $node->month);
        $this->assertEquals(9, $node->day);
        $this->assertEquals(3, $node->hour);
        $this->assertEquals(49, $node->minute);

        // Decending node
        $node = $halley->descendingNode();

        $this->assertEquals(1986, $node->year);
        $this->assertEquals(3, $node->month);
        $this->assertEquals(10, $node->day);
        $this->assertEquals(8, $node->hour);
        $this->assertEquals(51, $node->minute);

        // Parabolic
        $helin_roman = new Parabolic();
        $peridate = Carbon::create(1989, 8, 20, 6, 59, 2, 'UTC');
        $helin_roman->setOrbitalElements(1.324502, 0.0, 154.9103, 0.0, $peridate);

        // Ascending node
        $node = $helin_roman->ascendingNode();

        $this->assertEquals(1977, $node->year);
        $this->assertEquals(9, $node->month);
        $this->assertEquals(17, $node->day);
        $this->assertEquals(15, $node->hour);
        $this->assertEquals(21, $node->minute);

        // Decending node
        $node = $helin_roman->descendingNode();

        $this->assertEquals(1989, $node->year);
        $this->assertEquals(9, $node->month);
        $this->assertEquals(17, $node->day);
        $this->assertEquals(15, $node->hour);
        $this->assertEquals(16, $node->minute);
    }
}
