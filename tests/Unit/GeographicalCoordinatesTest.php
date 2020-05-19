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
        $this->assertEquals(15.748, $coords->getLongitude()->getCoordinate());
        $this->assertEquals(-5.42, $coords->getLatitude()->getCoordinate());

        $coords->setLatitude(15.2);
        $this->assertEquals(15.2, $coords->getLatitude()->getCoordinate());

        $coords->setLongitude(4.2);
        $this->assertEquals(4.2, $coords->getLongitude()->getCoordinate());

        $coords = new GeographicalCoordinates(95.748, 95.42);
        $this->assertEquals(-84.58, $coords->getLatitude()->getCoordinate());

        $coords = new GeographicalCoordinates(95.748, -95.42);
        $this->assertEquals(84.58, $coords->getLatitude()->getCoordinate());

        $coords = new GeographicalCoordinates(195.748, -5.42);
        $this->assertEquals(-164.252, $coords->getLongitude()->getCoordinate());

        $coords = new GeographicalCoordinates(-195.748, -5.42);
        $this->assertEquals(164.252, $coords->getLongitude()->getCoordinate());

        $coords = new GeographicalCoordinates(95.748, 5.42);
        $coords->setLatitude(-91.2);
        $this->assertEquals(88.8, $coords->getLatitude()->getCoordinate());

        $coords->setLatitude(92.5);
        $this->assertEquals(-87.5, $coords->getLatitude()->getCoordinate());

        $coords->setLongitude(-181.2);
        $this->assertEquals(178.8, $coords->getLongitude()->getCoordinate());

        $coords->setLongitude(181.2);
        $this->assertEquals(-178.8, $coords->getLongitude()->getCoordinate());
    }
}
