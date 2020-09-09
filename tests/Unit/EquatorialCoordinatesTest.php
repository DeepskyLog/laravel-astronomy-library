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

    /**
     * Test angular separation.
     *
     * @return None
     */
    public function testAngularSeparation()
    {
        // Arcturus and Spica
        $equa1 = new EquatorialCoordinates(14.2610277778, 19.1825);
        $equa2 = new EquatorialCoordinates(13.4198888, -11.1614);

        $this->assertEqualsWithDelta(
            $equa1->angularSeparation($equa2)->getCoordinate(),
            32.7930,
            0.0001
        );

        // Aldebaran and Antares
        $equa1 = new EquatorialCoordinates(4.598677519444444, 16.509302361111111);
        $equa2 = new EquatorialCoordinates(16.490127694444444, -26.432002611111111);

        $this->assertEqualsWithDelta(
            $equa1->angularSeparation($equa2)->getCoordinate(),
            169.9627,
            0.0001
        );
    }

    /**
     * Test bodies in straight line.
     *
     * @return None
     */
    public function testBodiesInStraightLine()
    {
        // Castor
        $castor = new EquatorialCoordinates(7.571222, 31.89756);
        // Pollux
        $pollux = new EquatorialCoordinates(7.750002778, 28.03681);

        // Mars on Sep 30, 1994
        $mars = new EquatorialCoordinates(7.97293055, 21.58983);
        $this->assertFalse($mars->isInStraightLine($castor, $pollux));

        // Mars on Oct 1, 1994, 5h TD
        $mars = new EquatorialCoordinates(8.022644129, 21.472188347);
        $this->assertTrue($mars->isInStraightLine($castor, $pollux));
    }

    /**
     * Test deviation of three bodies from a straight line.
     *
     * @return None
     */
    public function testDeviationFromStraightLine()
    {
        // Delta Ori
        $delta = new EquatorialCoordinates(5.5334444, -0.29913888);
        // Epsilon Ori
        $eps = new EquatorialCoordinates(5.60355833, -1.20194444);
        // Ksi Ori
        $ksi = new EquatorialCoordinates(5.679311111, -1.94258333);
        $this->assertEqualsWithDelta(
            $eps->deviationFromStraightLine($delta, $ksi)->getCoordinate(),
            0.089876,
            0.001
        );

        // Alpha Uma
        $alpha = new EquatorialCoordinates(11.062129444, 61.750894444);

        // Beta Uma
        $beta = new EquatorialCoordinates(11.030689444, 56.3824027778);

        // Polaris
        $polaris = new EquatorialCoordinates(2.530195556, 89.26408889);

        $this->assertEqualsWithDelta(
            $polaris->deviationFromStraightLine($alpha, $beta)->getCoordinate(),
            1.91853,
            0.001
        );
    }

    /**
     * Test smallest circle containing three celestial bodies.
     *
     * @return None
     */
    public function testSmallestCircle()
    {
        $coords1 = new EquatorialCoordinates(12.6857305, -5.631722);
        $coords2 = new EquatorialCoordinates(12.8681138, -4.373944);
        $coords3 = new EquatorialCoordinates(12.6578083, -1.834361);
        $this->assertEqualsWithDelta(
            $coords1->smallestCircle($coords2, $coords3)->getCoordinate(),
            4.26364,
            0.0001
        );

        $coords1 = new EquatorialCoordinates(9.094844, 18.50833);
        $coords2 = new EquatorialCoordinates(9.1580556, 17.732416);
        $coords3 = new EquatorialCoordinates(8.9964278, 17.826889);
        $this->assertEqualsWithDelta(
            $coords1->smallestCircle($coords2, $coords3)->getCoordinate(),
            2.31053754,
            0.0001
        );
    }

    /**
     * Test precession.
     *
     * @return None
     */
    public function testPrecessionLow()
    {
        $coords = new EquatorialCoordinates(
            10.13952778,
            11.967222,
            2000.0,
            -0.0169,
            0.006
        );
        $date = Carbon::createMidnightDate(1978, 1, 1);

        $precessed_coords = $coords->precession($date);
        $this->assertEqualsWithDelta(
            10.12002778,
            $precessed_coords->getRA()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            12.075416,
            $precessed_coords->getDeclination()->getCoordinate(),
            0.00001
        );
    }

    /**
     * Test precession.
     *
     * @return None
     */
    public function testPrecessionHigh()
    {
        // Test for theta Persei
        $coords = new EquatorialCoordinates(
            2.736662778,
            49.22846667,
            2000.0,
            0.03425,
            -0.0895
        );

        $date = Carbon::create(2028, 11, 13, 4, 33, 36, 'UTC');

        $precessed_coords = $coords->precessionHighAccuracy($date);
        $this->assertEqualsWithDelta(
            2.7698141667,
            $precessed_coords->getRA()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            49.34848333,
            $precessed_coords->getDeclination()->getCoordinate(),
            0.00001
        );

        // Test for polaris
        $coords = new EquatorialCoordinates(
            2.530195556,
            89.26408889,
            2000.0,
            0.19877,
            -0.0152
        );
        $date = Carbon::create(1900, 1, 1, 0, 0, 0, 'UTC');
        $precessed_coords = $coords->precessionHighAccuracy($date);
        $this->assertEqualsWithDelta(
            1.376083333,
            $precessed_coords->getRA()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            88.77393889,
            $precessed_coords->getDeclination()->getCoordinate(),
            0.00001
        );

        $date = Carbon::create(2050, 1, 1, 12, 0, 0, 'UTC');
        $precessed_coords = $coords->precessionHighAccuracy($date);
        $this->assertEqualsWithDelta(
            3.8046089,
            $precessed_coords->getRA()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            89.45427222,
            $precessed_coords->getDeclination()->getCoordinate(),
            0.00001
        );

        $date = Carbon::create(2100, 1, 1, 12, 0, 0, 'UTC');
        $precessed_coords = $coords->precessionHighAccuracy($date);
        $this->assertEqualsWithDelta(
            5.891436111,
            $precessed_coords->getRA()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            89.539494444,
            $precessed_coords->getDeclination()->getCoordinate(),
            0.00001
        );
    }
}
