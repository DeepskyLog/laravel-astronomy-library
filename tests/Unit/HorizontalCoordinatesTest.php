<?php
/**
 * Tests for the HorizontalCoordinates class.
 *
 * PHP Version 8
 *
 * @category Tests
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\HorizontalCoordinates;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the HorizontalCoordinates class.
 *
 * PHP Version 8
 *
 * @category Tests
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
class HorizontalCoordinatesTest extends BaseTestCase
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
     * Test setting and getting coordinates.
     *
     * @return None
     */
    public function testGetSetCoordinates()
    {
        $coords = new HorizontalCoordinates(15.748, -5.42);
        $this->assertEquals(15.748, $coords->getAzimuth()->getCoordinate());
        $this->assertEqualsWithDelta(-5.42, $coords->getAltitude()->getCoordinate(), 0.00001);

        $coords->setAzimuth(15.2);
        $this->assertEquals(15.2, $coords->getAzimuth()->getCoordinate());

        $coords->setAltitude(4.2);
        $this->assertEqualsWithDelta(4.2, $coords->getAltitude()->getCoordinate(), 0.00001);

        $coords = new HorizontalCoordinates(95.748, 95.42);
        $this->assertEqualsWithDelta(-84.58, $coords->getAltitude()->getCoordinate(), 0.00001);

        $coords = new HorizontalCoordinates(-19.748, -95.42);
        $this->assertEqualsWithDelta(84.58, $coords->getAltitude()->getCoordinate(), 0.00001);

        $coords = new HorizontalCoordinates(365.748, -5.42);
        $this->assertEqualsWithDelta(5.748, $coords->getAzimuth()->getCoordinate(), 0.00001);

        $coords = new HorizontalCoordinates(-1.748, -5.42);
        $this->assertEquals(358.252, $coords->getAzimuth()->getCoordinate());

        $coords->setAltitude(-91.2);
        $this->assertEqualsWithDelta(88.8, $coords->getAltitude()->getCoordinate(), 0.00001);

        $coords->setAltitude(92.5);
        $this->assertEquals(-87.5, $coords->getAltitude()->getCoordinate());

        $coords->setAzimuth(-1.2);
        $this->assertEquals(358.8, $coords->getAzimuth()->getCoordinate());

        $coords->setAzimuth(361.2);
        $this->assertEqualsWithDelta(1.2, $coords->getAzimuth()->getCoordinate(), 0.00001);
    }

    /**
     * Test conversion from horizontal coordinates to equatorial coordinates.
     *
     * @return None
     */
    public function testConversionToEquatorial()
    {
        $coords = new HorizontalCoordinates(68.0336, 15.1249);

        $date = Carbon::create(1987, 4, 10, 19, 21, 0, 'UTC');
        $location = new GeographicalCoordinates(-77.06555556, 38.92138889);
        $astrolib = new AstronomyLibrary($date, $location);
        $equa = $astrolib->horizontalToEquatorial($coords);

        $this->assertEqualsWithDelta(
            23.1546225,
            $equa->getRA()->getCoordinate(),
            0.0001
        );
        $this->assertEqualsWithDelta(
            -6.7198917,
            $equa->getDeclination()->getCoordinate(),
            0.0001
        );
    }

    /**
     * Test calculating refraction from horizontal coordinates.
     *
     * @return None
     */
    public function testRefraction()
    {
        $coords = new HorizontalCoordinates(68.0336, 0.5);

        $this->assertEqualsWithDelta(
            28.754,
            $coords->calculateRefractionFromApparentAltitude(),
            0.001
        );
    }
}
