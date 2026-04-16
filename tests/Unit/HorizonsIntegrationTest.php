<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HorizonsIntegrationTest extends TestCase
{
    public function testHorizonsHelperReturnsRaDecFor12P(): void
    {
        $script = __DIR__.'/../../scripts/horizons_radec.php';
        $cmd = escapeshellcmd(PHP_BINARY).' '.escapeshellarg($script).' '
            .escapeshellarg('12P').' '.escapeshellarg('2025-11-18 16:08').' '
            .escapeshellarg('4.84457').' '.escapeshellarg('49.3447').' '.escapeshellarg('130');

        $out = null;
        $ret = null;
        exec($cmd, $out, $ret);
        $this->assertSame(0, $ret, 'Helper script did not exit successfully');

        $json = @json_decode(implode("\n", $out), true);
        $this->assertIsArray($json, 'Expected JSON output from helper');
        $this->assertArrayHasKey('ra_hours', $json);
        $this->assertArrayHasKey('dec_deg', $json);

        // Expected authoritative values (user-provided)
        $expectedRa = 16 + 19 / 60 + 36.52 / 3600; // 16.326811111...
        $expectedDec = -36 - 20 / 60 - 0.4 / 3600; // -36.333444444...

        $this->assertLessThan(0.005, abs(floatval($json['ra_hours']) - $expectedRa), 'RA differs too much from expected');
        $this->assertLessThan(0.02, abs(floatval($json['dec_deg']) - $expectedDec), 'Dec differs too much from expected');
    }
}
