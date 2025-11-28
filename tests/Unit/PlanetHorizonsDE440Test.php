<?php

declare(strict_types=1);

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Mars;
use PHPUnit\Framework\TestCase;

final class PlanetHorizonsDE440Test extends TestCase
{
    public function testPlanetUsesHorizonsHelperWithDE440(): void
    {
        $script = __DIR__.'/../../scripts/horizons_radec.php';
        $dt = '2025-11-18 16:08';
        $lon = '4.84457';
        $lat = '49.3447';
        $alt = '130';

        // First: call the helper directly with EPHEM=DE440 to get authoritative RA/Dec
        $scriptPath = realpath($script);
        if (! $scriptPath || ! file_exists($scriptPath)) {
            $this->markTestSkipped('Horizons helper script not found');
        }

        $cmd = escapeshellcmd(PHP_BINARY).' '.escapeshellarg($scriptPath).' '
            .escapeshellarg('Mars').' '.escapeshellarg($dt).' '
            .escapeshellarg($lon).' '.escapeshellarg($lat).' '.escapeshellarg($alt).' '.escapeshellarg('DE440');

        $out = null;
        $ret = null;
        exec($cmd, $out, $ret);
        if ($ret !== 0) {
            $this->markTestSkipped('Horizons helper failed to produce JSON: '.implode("\n", $out));
        }

        $json = @json_decode(implode("\n", $out), true);
        if (! is_array($json) || ! isset($json['ra_hours'])) {
            $this->markTestSkipped('Invalid JSON from helper');
        }

        // Now request the same via the library Planet integration
        $date = Carbon::createFromFormat('Y-m-d H:i', $dt, 'UTC');
        $geo = new GeographicalCoordinates(floatval($lon), floatval($lat));

        $mars = new Mars();
        // Pass 'DE440' as the VSOP87/mode argument (third variadic arg)
        $mars->calculateEquatorialCoordinates($date->copy(), $geo, 130.0, 'DE440');

        $e = $mars->getEquatorialCoordinatesToday();
        $this->assertNotNull($e, 'Expected equatorial coordinates to be set');

        $ra = $e->getRA()->getCoordinate();
        $dec = $e->getDeclination()->getCoordinate();

        $this->assertLessThan(0.005, abs(floatval($json['ra_hours']) - $ra), 'RA differs too much from helper output');
        $this->assertLessThan(0.02, abs(floatval($json['dec_deg']) - $dec), 'Dec differs too much from helper output');
    }
}
