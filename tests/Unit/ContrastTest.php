<?php
/**
 * Tests calculating the contrast.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Tests\Unit;

use deepskylog\AstronomyLibrary\Targets\Target;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for calculating the contrast.
 *
 * PHP Version 7
 *
 * @category Tests
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class ContrastTest extends BaseTestCase
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
     * Test the calculation of SBObj.
     *
     * @return None
     */
    public function testSBObj()
    {
        $target = new Target();
        $target->setDiameter(8220);
        $this->assertNull($target->calculateSBObj());

        $target = new Target();
        $target->setMagnitude(12);
        $this->assertNull($target->calculateSBObj());

        $target = new Target();
        $target->setDiameter(8220);
        $target->setMagnitude(15);
        $this->assertEqualsWithDelta(34.3119, $target->calculateSBObj(), 0.001);

        $target->setDiameter(10800);
        $target->setMagnitude(8);
        $this->assertEqualsWithDelta(27.9047, $target->calculateSBObj(), 0.001);

        $target->setDiameter(55.98, 27.48);
        $target->setMagnitude(14.82);
        $this->assertEqualsWithDelta(22.5252, $target->calculateSBObj(), 0.001);

        $target->setDiameter(72, 54);
        $target->setMagnitude(12.4);
        $this->assertEqualsWithDelta(21.1119, $target->calculateSBObj(), 0.001);

        $target->setDiameter(3.5);
        $target->setMagnitude(7.4);
        $this->assertEqualsWithDelta(9.8579, $target->calculateSBObj(), 0.001);

        $target->setDiameter(17);
        $target->setMagnitude(8);
        $this->assertEqualsWithDelta(13.8898, $target->calculateSBObj(), 0.001);

        $target->setDiameter(46.998);
        $target->setMagnitude(18.3);
        $this->assertEqualsWithDelta(26.398, $target->calculateSBObj(), 0.001);

        $target->setDiameter(600);
        $target->setMagnitude(11);
        $this->assertEqualsWithDelta(24.6283, $target->calculateSBObj(), 0.001);

        $target->setDiameter(540, 138);
        $target->setMagnitude(9.2);
        $this->assertEqualsWithDelta(21.1182, $target->calculateSBObj(), 0.001);
    }

    /**
     * Test the calculation of the contrast.
     *
     * @return None
     */
    public function testCalculateContrast()
    {
        // Berk 59
        $target = new Target();
        $target->setDiameter(600);
        $target->setMagnitude(11);
        // Diameter telescope: 457mm
        // SQM / NELM location: 22 / 6.7
        $this->assertEqualsWithDelta(0.13, $target->calculateContrastReserve($target->calculateSBObj(), 22, 457, 118), 0.01);
        // SQM / NELM location: 20.15 / 5.6
        $this->assertEqualsWithDelta(-0.35, $target->calculateContrastReserve($target->calculateSBObj(), 20.15, 457, 473), 0.01);

        // M 65
        $target = new Target();
        $target->setDiameter(540, 138);
        $target->setMagnitude(9.2);
        // SBObj = 21.1182
        // Diameter telescope: 457mm
        // SQM / NELM location: 22 / 6.7
        $this->assertEqualsWithDelta(1.18, $target->calculateContrastReserve($target->calculateSBObj(), 22, 457, 66), 0.01);
        // SQM / NELM location: 20.15 / 5.6
        $this->assertEqualsWithDelta(0.70, $target->calculateContrastReserve($target->calculateSBObj(), 20.15, 457, 66), 0.01);

        // M 82
        $target = new Target();
        $target->setDiameter(630, 306);
        $target->setMagnitude(8.6);
        // SBObj = 21.5502
        // Diameter telescope: 457mm
        // SQM / NELM location: 22 / 6.7
        $this->assertEqualsWithDelta(1.20, $target->calculateContrastReserve($target->calculateSBObj(), 22, 457, 66), 0.01);
        // SQM / NELM location: 20.15 / 5.6
        $this->assertEqualsWithDelta(0.70, $target->calculateContrastReserve($target->calculateSBObj(), 20.15, 457, 66), 0.01);
    }

    /**
     * Test the calculation of the best detection magnification.
     *
     * @return None
     */
    public function testCalculateBestMagnification()
    {
        $magnifications = [
            66, 103, 158, 257, 411,
            76, 118, 182, 296, 473,
            133, 206, 317, 514, 823,
        ];

        // Berk 59
        $target = new Target();
        $target->setDiameter(600);
        $target->setMagnitude(11);
        // Diameter telescope: 457mm
        // SQM / NELM location: 22 / 6.7
        $this->assertEqualsWithDelta(133, $target->calculateBestMagnification($target->calculateSBObj(), 22, 457, $magnifications), 0.01);
        // SQM / NELM location: 20.15 / 5.6
        $this->assertEqualsWithDelta(473, $target->calculateBestMagnification($target->calculateSBObj(), 20.15, 457, $magnifications), 0.01);

        // M 65
        $target = new Target();
        $target->setDiameter(540, 138);
        $target->setMagnitude(9.2);
        // SBObj = 21.1182
        // Diameter telescope: 457mm
        // SQM / NELM location: 22 / 6.7
        $this->assertEqualsWithDelta(66, $target->calculateBestMagnification($target->calculateSBObj(), 22, 457, $magnifications), 0.01);
        // SQM / NELM location: 20.15 / 5.6
        $this->assertEqualsWithDelta(66, $target->calculateBestMagnification($target->calculateSBObj(), 20.15, 457, $magnifications), 0.01);

        // M 82
        $target = new Target();
        $target->setDiameter(630, 306);
        $target->setMagnitude(8.6);
        // SBObj = 21.5502
        // Diameter telescope: 457mm
        // SQM / NELM location: 22 / 6.7
        $this->assertEqualsWithDelta(66, $target->calculateBestMagnification($target->calculateSBObj(), 22, 457, $magnifications), 0.01);
        // SQM / NELM location: 20.15 / 5.6
        $this->assertEqualsWithDelta(66, $target->calculateBestMagnification($target->calculateSBObj(), 20.15, 457, $magnifications), 0.01);
    }
}
