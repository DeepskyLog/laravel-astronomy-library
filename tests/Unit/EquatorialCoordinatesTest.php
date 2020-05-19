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

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\HorizontalCoordinates;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

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
        $coords = new EquatorialCoordinates(15.748, -5.42);
        $this->assertEquals(15.748, $coords->getRA()->getCoordinate());
        $this->assertEquals(-5.42, $coords->getDeclination()->getCoordinate());

        $coords->setDeclination(15.2);
        $this->assertEquals(15.2, $coords->getDeclination()->getCoordinate());

        $coords->setRA(4.2);
        $this->assertEquals(4.2, $coords->getRA()->getCoordinate());

        $coords = new EquatorialCoordinates(25.748, 5.42);
        $this->assertEquals(1.748, $coords->getRA()->getCoordinate());

        $coords = new EquatorialCoordinates(-1.748, -5.42);
        $this->assertEquals(24 - 1.748, $coords->getRA()->getCoordinate());

        $coords = new EquatorialCoordinates(15.748, 95.42);
        $this->assertEquals(-84.58, $coords->getDeclination()->getCoordinate());

        $coords = new EquatorialCoordinates(15.748, -95.42);
        $this->assertEquals(84.58, $coords->getDeclination()->getCoordinate());

        $coords = new EquatorialCoordinates(5.748, 5.42);
        $coords->setDeclination(-91.2);
        $this->assertEquals(88.8, $coords->getDeclination()->getCoordinate());

        $coords->setDeclination(92.5);
        $this->assertEquals(-87.5, $coords->getDeclination()->getCoordinate());

        $coords->setRA(-1.2);
        $this->assertEquals(22.8, $coords->getRA()->getCoordinate());

        $coords->setRA(24.2);
        $this->assertEquals(0.2, $coords->getRA()->getCoordinate());
    }

    /**
     * Test conversion from equatorial coordinates to ecliptical coordinates.
     *
     * @return None
     */
    public function testConversionToEcliptical()
    {
        $coords = new EquatorialCoordinates(7.7552628, 28.026183);
        $ecl = $coords->convertToEclipticalJ2000();
        $this->assertEqualsWithDelta(
            113.215630,
            $ecl->getLongitude()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            6.684170,
            $ecl->getLatitude()->getCoordinate(),
            0.00001
        );
    }

    /**
     * Test conversion from equatorial coordinates to horizontal coordinates.
     *
     * @return None
     */
    public function testConversionToHorizontal()
    {
        $coords = new EquatorialCoordinates(23.1546225, -6.7198917);
        $date = Carbon::create(1987, 4, 10, 19, 21, 0, 'UTC');
        $location = new GeographicalCoordinates(-77.06555556, 38.92138889);
        $astrolib = new AstronomyLibrary($date, $location);
        $hor = $astrolib->equatorialToHorizontal($coords);
        $this->assertEqualsWithDelta(
            68.0336,
            $hor->getAzimuth()->getCoordinate(),
            0.0001
        );
        $this->assertEqualsWithDelta(
            15.1249,
            $hor->getAltitude()->getCoordinate(),
            0.0001
        );
    }

    /**
     * Test conversion from equatorial coordinates to galactic coordinates.
     *
     * @return None
     */
    public function testConversionToGalactic()
    {
        $coords = new EquatorialCoordinates(23.1546225, -6.7198917);
        $gal = $coords->convertToGalactic();
        $this->assertEqualsWithDelta(
            68.34653864,
            $gal->getLongitude()->getCoordinate(),
            0.0001
        );
        $this->assertEqualsWithDelta(
            -58.30545704,
            $gal->getLatitude()->getCoordinate(),
            0.0001
        );
    }

    /**
     * Test parallactic angle.
     *
     * @return None
     */
    public function testParallacticAngle()
    {
        $date = Carbon::now();
        $geo = new GeographicalCoordinates(12.12, 45.12);
        $astrolib = new AstronomyLibrary($date, $geo);
        $hor = new HorizontalCoordinates(0, 15);
        $equa = $astrolib->horizontalToEquatorial($hor);
        $this->assertEquals(
            0.0,
            $equa->getParallacticAngle($geo, $astrolib->getApparentSiderialTime())
        );

        $hor = new HorizontalCoordinates(10, 25);
        $equa = $astrolib->horizontalToEquatorial($hor);
        $this->assertGreaterThan(
            0.0,
            $equa->getParallacticAngle($geo, $astrolib->getApparentSiderialTime())
        );

        $hor = new HorizontalCoordinates(-10, 25);
        $equa = $astrolib->horizontalToEquatorial($hor);
        $this->assertLessThan(
            0.0,
            $equa->getParallacticAngle($geo, $astrolib->getApparentSiderialTime())
        );
    }
}
