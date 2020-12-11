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
}
