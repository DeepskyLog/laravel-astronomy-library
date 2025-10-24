<?php

/**
 * Tests for the magnitude methods.
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

use deepskylog\AstronomyLibrary\Magnitude;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the magnitude methods.
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
class MagnitudeTest extends BaseTestCase
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
     * Test converting from NELM to SQM.
     *
     * @return None
     */
    public function testConvertNelmToSqm()
    {
        $this->assertEquals(22.0, Magnitude::nelmToSqm(6.7));
        $this->assertEqualsWithDelta(16.88, Magnitude::nelmToSqm(3.0), 0.01);
        $this->assertEqualsWithDelta(18.03, Magnitude::nelmToSqm(4.0), 0.01);
        $this->assertEqualsWithDelta(18.65, Magnitude::nelmToSqm(4.5), 0.01);
        $this->assertEqualsWithDelta(19.30, Magnitude::nelmToSqm(5.0), 0.01);
        $this->assertEqualsWithDelta(20.01, Magnitude::nelmToSqm(5.5), 0.01);
        $this->assertEqualsWithDelta(20.47, Magnitude::nelmToSqm(5.8), 0.01);
        $this->assertEqualsWithDelta(20.80, Magnitude::nelmToSqm(6.0), 0.01);
        $this->assertEqualsWithDelta(21.15, Magnitude::nelmToSqm(6.2), 0.01);
        $this->assertEqualsWithDelta(21.53, Magnitude::nelmToSqm(6.4), 0.01);
        $this->assertEqualsWithDelta(21.73, Magnitude::nelmToSqm(6.5), 0.01);
        $this->assertEqualsWithDelta(21.94, Magnitude::nelmToSqm(6.6), 0.01);
    }

    /**
     * Test exceptions for wrong sqm.
     *
     * @return None
     */
    public function testWrongNelmToSqm()
    {
        $this->expectException(\InvalidArgumentException::class);
        Magnitude::nelmToSqm(9.5);
    }

    /**
     * Test exceptions for wrong sqm.
     *
     * @return None
     */
    public function testWrongNelmToSqm2()
    {
        $this->expectException(\InvalidArgumentException::class);
        Magnitude::nelmToSqm(-2);
    }

    /**
     * Test converting from SQM to NELM.
     *
     * @return None
     */
    public function testConvertSqmToNelm()
    {
        $this->assertEqualsWithDelta(6.62, Magnitude::sqmToNelm(22.0), 0.01);
        $this->assertEqualsWithDelta(6.6, Magnitude::sqmToNelm(21.94), 0.01);
        $this->assertEqualsWithDelta(6.5, Magnitude::sqmToNelm(21.73), 0.01);
        $this->assertEqualsWithDelta(6.4, Magnitude::sqmToNelm(21.53), 0.01);
        $this->assertEqualsWithDelta(6.2, Magnitude::sqmToNelm(21.15), 0.01);
        $this->assertEqualsWithDelta(6.0, Magnitude::sqmToNelm(20.80), 0.01);
        $this->assertEqualsWithDelta(5.8, Magnitude::sqmToNelm(20.47), 0.01);
        $this->assertEqualsWithDelta(5.5, Magnitude::sqmToNelm(20.01), 0.01);
        $this->assertEqualsWithDelta(5.0, Magnitude::sqmToNelm(19.30), 0.01);
        $this->assertEqualsWithDelta(4.5, Magnitude::sqmToNelm(18.65), 0.01);
        $this->assertEqualsWithDelta(4.0, Magnitude::sqmToNelm(18.03), 0.01);
        $this->assertEqualsWithDelta(3.0, Magnitude::sqmToNelm(16.88), 0.01);
    }

    /**
     * Test converting from Nelm to Sqm and back.
     *
     * @return None
     */
    public function testConvertNelmToSqmAndBack()
    {
        $this->assertEqualsWithDelta(20.008595203233345, Magnitude::nelmToSqm(5.5), 0.000001);
        $this->assertEqualsWithDelta(5.5, Magnitude::sqmToNelm(20.008595203233345), 0.000001);
    }
}
