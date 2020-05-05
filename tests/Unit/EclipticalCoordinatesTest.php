<?php
/**
 * Tests for the EclipticalCoordinates class.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the EclipticalCoordinates class.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EclipticalCoordinatesTest extends BaseTestCase
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
     * Test setting and getting coordinates.
     *
     * @return None
     */
    public function testGetSetCoordinates()
    {
        $coords = new EclipticalCoordinates(15.748, -5.42);
        $this->assertEquals(15.748, $coords->getLongitude());
        $this->assertEquals(-5.42, $coords->getLatitude());

        $coords->setLatitude(15.2);
        $this->assertEquals(15.2, $coords->getLatitude());

        $coords->setLongitude(4.2);
        $this->assertEquals(4.2, $coords->getLongitude());

        $coords = new EclipticalCoordinates(95.748, 95.42);
        $this->assertEquals(-84.58, $coords->getLatitude());

        $coords = new EclipticalCoordinates(-19.748, -95.42);
        $this->assertEquals(84.58, $coords->getLatitude());

        $coords = new EclipticalCoordinates(365.748, -5.42);
        $this->assertEquals(5.748, $coords->getLongitude());

        $coords = new EclipticalCoordinates(-1.748, -5.42);
        $this->assertEquals(358.252, $coords->getLongitude());

        $coords->setLatitude(-91.2);
        $this->assertEquals(88.8, $coords->getLatitude());

        $coords->setLatitude(92.5);
        $this->assertEquals(-87.5, $coords->getLatitude());

        $coords->setLongitude(-1.2);
        $this->assertEquals(358.8, $coords->getLongitude());

        $coords->setLongitude(361.2);
        $this->assertEquals(1.2, $coords->getLongitude());
    }

    /**
     * Test conversion from ecliptical coordinates to equatorial coordinates.
     *
     * @return None
     */
    public function testConversionToEquatorial()
    {
        $coords = new EclipticalCoordinates(113.215630, 6.684170);
        $equa = $coords->convertToEquatorialJ2000();
        $this->assertEqualsWithDelta(7.7552628, $equa->getRa(), 0.00001);
        $this->assertEqualsWithDelta(28.026183, $equa->getDeclination(), 0.00001);
    }
}
