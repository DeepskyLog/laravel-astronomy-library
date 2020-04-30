<?php
/**
 * Tests for the EquatorialCoordinates class.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use deepskylog\AstronomyLibrary\Testing\BaseTestCase;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;

/**
 * Tests for the EquatorialCoordinates class.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class EquatorialCoordinatesTest extends BaseTestCase
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
        $coords = new EquatorialCoordinates(15.748, -5.42);
        $this->assertEquals(15.748, $coords->getRA());
        $this->assertEquals(-5.42, $coords->getDeclination());

        $coords->setDeclination(15.2);
        $this->assertEquals(15.2, $coords->getDeclination());

        $coords->setRA(4.2);
        $this->assertEquals(4.2, $coords->getRA());
    }

    /**
     * Test exceptions for wrong Right Ascension.
     *
     * @return None
     */
    public function testWrongRA()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(25.748, 5.42);
    }

    /**
     * Test exceptions for wrong Right Ascension.
     *
     * @return None
     */
    public function testWrongRA2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(-1.748, -5.42);
    }

    /**
     * Test exceptions for wrong declination.
     *
     * @return None
     */
    public function testWrongDeclination()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(15.748, 95.42);
    }

    /**
     * Test exceptions for wrong declination.
     *
     * @return None
     */
    public function testWrongDeclination2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(15.748, -95.42);
    }

    /**
     * Test exceptions for wrong declination.
     *
     * @return None
     */
    public function testSetWrongDeclination()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(5.748, 5.42);
        $coords->setDeclination(-91.2);
    }

    /**
     * Test exceptions for wrong declination.
     *
     * @return None
     */
    public function testSetWrongDeclination2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(19.748, -9.42);
        $coords->setDeclination(92.5);
    }

    /**
     * Test exceptions for wrong right ascension.
     *
     * @return None
     */
    public function testSetWrongRA()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(15.748, -5.42);
        $coords->setRA(-1.2);
    }

    /**
     * Test exceptions for wrong right ascension.
     *
     * @return None
     */
    public function testSetWrongLongitude2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $coords = new EquatorialCoordinates(15.748, -5.42);
        $coords->setRA(24.2);
    }
}
