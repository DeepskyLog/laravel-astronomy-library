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
    }

    /**
     * Test exceptions for wrong latitude.
     *
     * @return None
     */
    public function testWrongLatitude()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(95.748, 95.42);
    }

    /**
     * Test exceptions for wrong latitude.
     *
     * @return None
     */
    public function testWrongLatitude2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(-19.748, -95.42);
    }

    /**
     * Test exceptions for wrong longitude.
     *
     * @return None
     */
    public function testWrongLongitude()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(365.748, -5.42);
    }

    /**
     * Test exceptions for wrong longitude.
     *
     * @return None
     */
    public function testWrongLongitude2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(-1.748, -5.42);
    }

    /**
     * Test exceptions for wrong latitude.
     *
     * @return None
     */
    public function testSetWrongLatitude()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(95.748, 5.42);
        $coords->setLatitude(-91.2);
    }

    /**
     * Test exceptions for wrong latitude.
     *
     * @return None
     */
    public function testSetWrongLatitude2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(-19.748, -9.42);
        $coords->setLatitude(92.5);
    }

    /**
     * Test exceptions for wrong longitude.
     *
     * @return None
     */
    public function testSetWrongLongitude()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(15.748, -5.42);
        $coords->setLongitude(-1.2);
    }

    /**
     * Test exceptions for wrong longitude.
     *
     * @return None
     */
    public function testSetWrongLongitude2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EclipticalCoordinates(-15.748, -5.42);
        $coords->setLongitude(361.2);
    }
}
