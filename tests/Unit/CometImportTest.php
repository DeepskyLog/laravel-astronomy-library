<?php

/**
 * Tests for the parsing of the orbital elements and the photometry of comets.
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

use deepskylog\AstronomyLibrary\Commands\UpdateCometPhotometry;
use deepskylog\AstronomyLibrary\Commands\UpdateOrbitalElements;
use deepskylog\AstronomyLibrary\Testing\BaseTestCase;

/**
 * Tests for the parsing of the orbital elements and the photometry of comets.
 *
 * @category Tests
 *
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 *
 * @link     http://www.deepskylog.org
 */
class CometImportTest extends BaseTestCase
{
    protected $appPath = __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php';

    /**
     * Test a line of CometEls.txt of the Minor Planet Center.
     */
    public function testParseMpcComet()
    {
        $line = '0010P         2026 08  2.1166  1.417741  0.537437  195.4699  117.7969   12.0271  20260924  13.1  4.0  10P/Tempel                                               MPC xxxxx';
        $comet = UpdateOrbitalElements::parseMpcComet($line);

        $this->assertEquals('10P/Tempel', $comet['name']);
        $this->assertEqualsWithDelta(20260802.1166, $comet['Tp'], 1e-6);
        $this->assertEquals(1.417741, $comet['q']);
        $this->assertEquals(0.537437, $comet['e']);
        $this->assertEquals(195.4699, $comet['w']);
        $this->assertEquals(117.7969, $comet['node']);
        $this->assertEquals(12.0271, $comet['i']);
        // 2026 September 24, 0h
        $this->assertEqualsWithDelta(2461307.5, $comet['epoch'], 1e-6);
        $this->assertEquals('MPC xxxxx', $comet['ref']);

        $line = '    CK25A060  2025 11  8.5253  0.529829  0.995619  132.9562  108.0979  143.6635  20260924  13.2  4.0  C/2025 A6 (Lemmon)                                       MPEC 2026-P55';
        $comet = UpdateOrbitalElements::parseMpcComet($line);
        $this->assertEquals('C/2025 A6 (Lemmon)', $comet['name']);
        $this->assertEqualsWithDelta(20251108.5253, $comet['Tp'], 1e-6);
        $this->assertEquals('MPC MPEC 2026-P55', $comet['ref']);
        $this->assertTrue($comet['has_epoch']);

        // Without an epoch the elements are for the perihelion
        $line = '0073P      g  2006 06  8.096   0.93920   0.69330   198.772    69.909    11.388                        73P-G/Schwassmann-Wachmann                               M';
        $comet = UpdateOrbitalElements::parseMpcComet($line);
        $this->assertEquals('73P-G/Schwassmann-Wachmann', $comet['name']);
        $this->assertFalse($comet['has_epoch']);
        // 2006 June 8.096
        $this->assertEqualsWithDelta(2453894.596, $comet['epoch'], 1e-3);

        $this->assertNull(UpdateOrbitalElements::parseMpcComet(''));
    }

    /**
     * Test that the names of JPL and the Minor Planet Center give the same designation.
     */
    public function testDesignation()
    {
        $this->assertEquals('10P', UpdateOrbitalElements::designation('10P/Tempel 2'));
        $this->assertEquals('10P', UpdateOrbitalElements::designation('10P/Tempel'));
        $this->assertEquals('29P', UpdateOrbitalElements::designation('29P/Schwassmann-Wachmann 1'));
        $this->assertEquals('67P', UpdateOrbitalElements::designation('67P/Churyumov-Gerasimenko'));
        $this->assertEquals('73P-B', UpdateOrbitalElements::designation('73P/Schwassmann-Wachmann 3-B'));
        $this->assertEquals('73P-B', UpdateOrbitalElements::designation('73P-B/Schwassmann-Wachmann'));
        $this->assertEquals('101P-B', UpdateOrbitalElements::designation('101P/Chernykh-B'));
        $this->assertEquals('101P', UpdateOrbitalElements::designation('101P/Chernykh'));
        $this->assertEquals('123P', UpdateOrbitalElements::designation('123P/West-Hartley'));
        $this->assertEquals('C/2025 A6', UpdateOrbitalElements::designation('C/2025 A6 (Lemmon)'));
        $this->assertEquals('C/2025 K1-B', UpdateOrbitalElements::designation('C/2025 K1-B (ATLAS)'));
        $this->assertEquals('A/2018 W3', UpdateOrbitalElements::designation('A/2018 W3'));
        $this->assertNull(UpdateOrbitalElements::designation('Nishimura'));
    }

    /**
     * Test the light curve of aerith.net, in parts.
     */
    public function testParseAerithLightCurve()
    {
        $html = <<<'HTML'
<PRE>
  H = 12.5  G = 0.15                       [    ,-840]  (             - 2022 Jan.  2)
  m1 = 5.0 + 5 log d + 13.5 log r          [-840,-276]  (2022 Jan.  2 - 2023 July 20)
  m1 = 9.0 + 5 log d +  0.0 log r          [-276,-158]  (2023 July 20 - 2023 Nov. 15)
  m1 = 8.5 + 5 log d -  5.0 log r          [-158,-100]  (2023 Nov. 15 - 2024 Jan. 12)
  m1 = 4.6 + 5 log d +  9.5 log r          [-100,  14]  (2024 Jan. 12 - 2024 May   5)
  m1 = 4.3 + 5 log d + 11.0 log r(t + 10)  [  14,    ]  (2024 May   5 - 2024 Oct. 13)
  m1 = 5.0 + 5 log d + 13.5 log r          [ 175,    ]  (2024 Oct. 13 -             )

* Gray curve is:  m1 = 6.0 + 5 log d + 10.0 log r
</PRE>
HTML;

        $found = UpdateCometPhotometry::parseAerithPhotometry($html);

        // H and K of the most recent part
        $this->assertEquals(5.0, $found['H']);
        $this->assertEquals(13.5, $found['K']);

        $parts = $found['light_curve'];
        $this->assertCount(7, $parts);
        $this->assertEquals(['from' => null, 'to' => -840.0, 'H' => 12.5, 'K' => 5.0, 'shift' => 0.0], $parts[0]);
        $this->assertEquals(-5.0, $parts[3]['K']);
        // The open end of [14, ] runs to the start of the next part
        $this->assertEquals(['from' => 14.0, 'to' => 175.0, 'H' => 4.3, 'K' => 11.0, 'shift' => 10.0], $parts[5]);
        $this->assertEquals(['from' => 175.0, 'to' => null, 'H' => 5.0, 'K' => 13.5, 'shift' => 0.0], $parts[6]);

        $this->assertEquals(-20.0, UpdateCometPhotometry::parseAerithPhotometry('  m1 = 7.9 + 5 log d + 19.5 log r(t - 20)  [  ,20]')['light_curve'][0]['shift']);
        $this->assertNull(UpdateCometPhotometry::parseAerithPhotometry('<P>No magnitudes yet.</P>'));
    }
}
