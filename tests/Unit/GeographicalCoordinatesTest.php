<?php
/**
 * Tests for the GeographicalCoordinates class.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the GeographicalCoordinates class.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class GeographicalCoordinatesTest extends BaseTestCase
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
        $coords = new GeographicalCoordinates(15.748, -5.42);
        $this->assertEqualsWithDelta(15.748, $coords->getLongitude()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(-5.42, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLatitude(15.2);
        $this->assertEqualsWithDelta(15.2, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(4.2);
        $this->assertEqualsWithDelta(4.2, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new GeographicalCoordinates(95.748, 95.42);
        $this->assertEqualsWithDelta(-84.58, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords = new GeographicalCoordinates(95.748, -95.42);
        $this->assertEqualsWithDelta(84.58, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords = new GeographicalCoordinates(195.748, -5.42);
        $this->assertEqualsWithDelta(-164.252, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new GeographicalCoordinates(-195.748, -5.42);
        $this->assertEqualsWithDelta(164.252, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new GeographicalCoordinates(95.748, 5.42);
        $coords->setLatitude(-91.2);
        $this->assertEqualsWithDelta(88.8, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLatitude(92.5);
        $this->assertEqualsWithDelta(-87.5, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(-181.2);
        $this->assertEqualsWithDelta(178.8, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(181.2);
        $this->assertEqualsWithDelta(-178.8, $coords->getLongitude()->getCoordinate(), 0.00001);
    }

    /**
     * Test calculating rhoSinPhi and rhoCosPhi.
     *
     * @return None
     */
    public function testEarthsGlobe()
    {
        $coords = new GeographicalCoordinates(-7.790833333333333, 33.356111111111111);
        $earthsGlobe = $coords->earthsGlobe(1706);
        $this->assertEqualsWithDelta(0.546861, $earthsGlobe[0], 0.000001);
        $this->assertEqualsWithDelta(0.836339, $earthsGlobe[1], 0.000001);
    }
}
