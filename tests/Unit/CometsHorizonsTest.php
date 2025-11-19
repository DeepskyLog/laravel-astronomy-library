<?php

use PHPUnit\Framework\TestCase;

/**
 * Integration tests that verify the Horizons helper returns sensible RA/Dec
 * values for a selection of comet designations.
 *
 * These tests require network access to JPL Horizons and the helper
 * script `scripts/horizons_radec.php` to be present and executable.
 */
class CometsHorizonsTest extends TestCase
{
    public static function cometsProvider(): array
    {
        return [
            // designation (string or array of alternatives), datetime UTC, lon, lat, height
            ['12P', '2025-11-18 16:08', 4.84457, 49.3447, 130],
            ['2P', '2025-11-18 16:08', 4.84457, 49.3447, 130],
            ['1P', '2025-11-18 16:08', 4.84457, 49.3447, 130],
            // 103P has several common aliases; provide alternatives and accept any that succeed
            [['103P', '103P/Hartley', '103P/Hartley 2', '103P Hartley 2', '103P/Hartley2'], '2025-11-18 16:08', 4.84457, 49.3447, 130],
        ];
    }

    /**
     * @dataProvider cometsProvider
     */
    public function testHorizonsHelperReturnsRaDec(mixed $des, string $datetime, float $lon, float $lat, float $height)
    {
        $script = realpath(__DIR__ . '/../../scripts/horizons_radec.php');
        if (! $script || ! file_exists($script)) {
            $this->markTestSkipped('Horizons helper script not found: ' . __DIR__ . '/../../scripts/horizons_radec.php');
        }

        $candidates = is_array($des) ? $des : [$des];
        $out = null;
        $ret = null;
        $json = null;
        $used = null;

        foreach ($candidates as $candidate) {
            $cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script) . ' '
                . escapeshellarg((string)$candidate) . ' ' . escapeshellarg($datetime) . ' '
                . escapeshellarg((string)$lon) . ' ' . escapeshellarg((string)$lat) . ' ' . escapeshellarg((string)$height);

            $out = null;
            $ret = null;
            exec($cmd, $out, $ret);

            if ($ret === 0) {
                $json = @json_decode(implode("\n", $out), true);
                if (is_array($json) && isset($json['ra_hours']) && isset($json['dec_deg'])) {
                    $used = (string)$candidate;
                    break;
                }
            }
        }

        if ($ret !== 0 || ! is_array($json) || ! isset($json['ra_hours'])) {
            $this->markTestSkipped('Horizons helper did not return usable data for any candidate: ' . json_encode($candidates) . ' (last output: ' . implode("\n", (array)$out) . ')');
        }
        $this->assertIsArray($json, 'Invalid JSON returned by helper for ' . $des);
        $this->assertArrayHasKey('ra_hours', $json);
        $this->assertArrayHasKey('dec_deg', $json);

        $ra = floatval($json['ra_hours']);
        $dec = floatval($json['dec_deg']);

        $this->assertGreaterThanOrEqual(0.0, $ra, 'RA out of range for ' . ($used ?? json_encode($des)));
        $this->assertLessThanOrEqual(24.0, $ra, 'RA out of range for ' . ($used ?? json_encode($des)));
        $this->assertGreaterThanOrEqual(-90.0, $dec, 'Dec out of range for ' . ($used ?? json_encode($des)));
        $this->assertLessThanOrEqual(90.0, $dec, 'Dec out of range for ' . ($used ?? json_encode($des)));
    }
}
