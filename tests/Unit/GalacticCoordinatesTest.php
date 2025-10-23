<?php

/**
 * Tests for the GalacticCoordinates class.
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

use deepskylog\AstronomyLibrary\Coordinates\GalacticCoordinates;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the GalacticCoordinates class.
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
class GalacticCoordinatesTest extends BaseTestCase
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
        $coords = new GalacticCoordinates(15.748, -5.42);
        $this->assertEqualsWithDelta(15.748, $coords->getLongitude()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(-5.42, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLatitude(15.2);
        $this->assertEqualsWithDelta(15.2, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(4.2);
        $this->assertEqualsWithDelta(4.2, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new GalacticCoordinates(95.748, 95.42);
        $this->assertEqualsWithDelta(-84.58, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords = new GalacticCoordinates(-19.748, -95.42);
        $this->assertEqualsWithDelta(84.58, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords = new GalacticCoordinates(365.748, -5.42);
        $this->assertEqualsWithDelta(5.748, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new GalacticCoordinates(-1.748, -5.42);
        $this->assertEqualsWithDelta(358.252, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords->setLatitude(-91.2);
        $this->assertEqualsWithDelta(88.8, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLatitude(92.5);
        $this->assertEqualsWithDelta(-87.5, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(-1.2);
        $this->assertEqualsWithDelta(358.8, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(361.2);
        $this->assertEqualsWithDelta(1.2, $coords->getLongitude()->getCoordinate(), 0.00001);
    }

    /**
     * Test conversion from galactic coordinates to equatorial coordinates.
     *
     * @return None
     */
    public function testConversionToEquatorial()
    {
        $coords = new GalacticCoordinates(68.34653864, -58.30545704);
        $equa = $coords->convertToEquatorial();

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
}
