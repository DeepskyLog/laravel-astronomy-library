<?php

/**
 * Tests for the sun class.
 *
 * PHP Version 8
 *
 * @category Tests
 *
 * @author Deepsky Developers <developers@deepskylog.be>
 * @license GPL3 <https: //opensource.org/licenses/GPL-3.0>
 *
 * @see http://www.deepskylog.org
 */

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use deepskylog\AstronomyLibrary\Targets\Sun;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;
use deepskylog\AstronomyLibrary\Time;

/**
 * Tests for the sun class.
 *
 * PHP Version 8
 *
 * @category Tests
 *
 * @author Deepsky Developers <developers@deepskylog.be>
 * @license GPL3 <https: //opensource.org/licenses/GPL-3.0>
 *
 * @see http://www.deepskylog.org
 */
class SunTest extends BaseTestCase
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
     * Test sun class.
     *
     * @return None
     */
    public function testSunClass()
    {
        $sun = new Sun();
        $this->assertEquals(-0.8333, $sun->getH0());
    }

    public function testEquatorialCoordinates()
    {
        $sun = new Sun();
        $date = Carbon::create(1992, 10, 13, 0, 0, 0, 'UTC');

        $nutation = Time::nutation(Time::getJd($date));
        $sun->calculateEquatorialCoordinates($date, $nutation[3]);
        $coordinates = $sun->getEquatorialCoordinates();

        $this->assertEqualsWithDelta(13.225445021, $coordinates->getRA()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(-7.785469, $coordinates->getDeclination()->getCoordinate(), 0.00001);
    }

    public function testEquatorialCoordinatesHighAccuracy()
    {
        $sun = new Sun();
        $date = Carbon::create(1992, 10, 13, 0, 0, 0, 'UTC');

        $nutation = Time::nutation(Time::getJd($date));
        $sun->calculateEquatorialCoordinatesHighAccuracy($date, $nutation);
        $coordinates = $sun->getEquatorialCoordinates();

        $this->assertEqualsWithDelta(13.22521187, $coordinates->getRA()->getCoordinate(), 0.000001);
        $this->assertEqualsWithDelta(-7.783871, $coordinates->getDeclination()->getCoordinate(), 0.000001);
    }

    public function testRectangularCoordinates()
    {
        $sun = new Sun();
        $date = Carbon::create(1992, 10, 13, 0, 0, 0, 'UTC');

        $rect_coords = $sun->calculateGeometricCoordinates($date);

        $this->assertEqualsWithDelta(-0.9379952, $rect_coords->getX()->getCoordinate(), 0.0000001);
        $this->assertEqualsWithDelta(-0.3116544, $rect_coords->getY()->getCoordinate(), 0.0000001);
        $this->assertEqualsWithDelta(-0.1351215, $rect_coords->getZ()->getCoordinate(), 0.0000001);
    }

    public function testRectangularCoordinatesJ2000()
    {
        $sun = new Sun();
        $date = Carbon::create(1992, 10, 13, 0, 0, 0, 'UTC');

        $rect_coords = $sun->calculateGeometricCoordinatesJ2000($date);

        $this->assertEqualsWithDelta(-0.93739590, $rect_coords->getX()->getCoordinate(), 0.0000001);
        $this->assertEqualsWithDelta(-0.31316793, $rect_coords->getY()->getCoordinate(), 0.0000001);
        $this->assertEqualsWithDelta(-0.13577924, $rect_coords->getZ()->getCoordinate(), 0.0000001);
    }

    public function testEquationOfTime()
    {
        $sun = new Sun();
        $date = Carbon::create(1992, 10, 13, 0, 0, 0, 'UTC');

        $equationOfTime = $sun->calculateEquationOfTime($date);

        $this->assertEquals(13.7090, $equationOfTime);
    }

    public function testPhysicalEphemeris()
    {
        $sun = new Sun();
        $date = Carbon::create(1992, 10, 13, 0, 0, 0, 'UTC');

        $ephemeris = $sun->getPhysicalEphemeris($date, 59);

        $this->assertEqualsWithDelta(26.27, $ephemeris[0], 0.01);
        $this->assertEqualsWithDelta(5.99, $ephemeris[1], 0.01);
        $this->assertEqualsWithDelta(238.63, $ephemeris[2], 0.01);
    }
}
