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

use Carbon\Carbon;
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
        $coords = new EclipticalCoordinates(15.748, -5.42);
        $this->assertEqualsWithDelta(15.748, $coords->getLongitude()->getCoordinate(), 0.00001);
        $this->assertEqualsWithDelta(-5.42, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLatitude(15.2);
        $this->assertEqualsWithDelta(15.2, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords->setLongitude(4.2);
        $this->assertEqualsWithDelta(4.2, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new EclipticalCoordinates(95.748, 95.42);
        $this->assertEqualsWithDelta(-84.58, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords = new EclipticalCoordinates(-19.748, -95.42);
        $this->assertEqualsWithDelta(84.58, $coords->getLatitude()->getCoordinate(), 0.00001);

        $coords = new EclipticalCoordinates(365.748, -5.42);
        $this->assertEqualsWithDelta(5.748, $coords->getLongitude()->getCoordinate(), 0.00001);

        $coords = new EclipticalCoordinates(-1.748, -5.42);
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
     * Test conversion from ecliptical coordinates to equatorial coordinates.
     *
     * @return None
     */
    public function testConversionToEquatorial()
    {
        $coords = new EclipticalCoordinates(113.215630, 6.684170);
        $equa = $coords->convertToEquatorialJ2000();
        $this->assertEqualsWithDelta(
            7.7552628,
            $equa->getRa()->getCoordinate(),
            0.00001
        );
        $this->assertEqualsWithDelta(
            28.026183,
            $equa->getDeclination()->getCoordinate(),
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
        // Test for Venus
        $coords = new EclipticalCoordinates(
            149.48194,
            1.76549,
            2000.0
        );

        $date = Carbon::create(-214, 6, 30, 0, 0, 0, 'UTC');

        $precessed_coords = $coords->precessionHighAccuracy($date);
        $this->assertEqualsWithDelta(
            118.704,
            $precessed_coords->getLongitude()->getCoordinate(),
            0.001
        );
        $this->assertEqualsWithDelta(
            1.615,
            $precessed_coords->getLatitude()->getCoordinate(),
            0.001
        );
    }
}
