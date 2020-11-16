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
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Targets\Moon;
use deepskylog\AstronomyLibrary\Targets\Planet;
use deepskylog\AstronomyLibrary\Targets\Target;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;
use deepskylog\AstronomyLibrary\Coordinates\Coordinate;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

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
        $date          = Carbon::create(1988, 3, 20, 12);
        $geo_coords    = new GeographicalCoordinates(-71.0833, 42.3333);
        $equaToday     = new EquatorialCoordinates(2.782086, 18.44092);
        $equaTomorrow  = new EquatorialCoordinates(2.852136, 18.82742);
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
        $date          = Carbon::create(2020, 5, 18, 12);
        $geo_coords    = new GeographicalCoordinates(4.86463, 50.83220);
        $equaToday     = new EquatorialCoordinates(5.33815, 26.8638);
        $equaTomorrow  = new EquatorialCoordinates(5.3236, 26.7175);
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
        $date       = Carbon::create(1988, 3, 20, 12);
        $geo_coords = new GeographicalCoordinates(-71.0833, 42.3333);
        $equa       = new EquatorialCoordinates(2.852136, -78.82742);

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
        $date       = Carbon::create(1988, 3, 20, 12);
        $geo_coords = new GeographicalCoordinates(-71.0833, 42.3333);
        $equa       = new EquatorialCoordinates(2.852136, 85.82742);

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
        $date       = Carbon::create(2020, 5, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa       = new EquatorialCoordinates(13.703055555555556, 28.37555556);

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
        $date       = Carbon::create(2020, 5, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa       = new EquatorialCoordinates(16.695, 36.460278);

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
        $equa       = new EquatorialCoordinates(16.695, 36.460278);

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
        $date       = Carbon::create(2020, 6, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 50.83220);
        $equa       = new EquatorialCoordinates(16.695, 36.460278);

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
        $date       = Carbon::create(2020, 6, 13, 12);
        $geo_coords = new GeographicalCoordinates(4.86463, 80.83220);
        $equa       = new EquatorialCoordinates(16.695, 36.460278);

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
}
