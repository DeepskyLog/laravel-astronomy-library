<?php

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;

class EllipticHorizonsIntegrationTest extends TestCase
{
    public function testEllipticUsesHorizonsHelper()
    {
        $script = realpath(__DIR__ . '/../../scripts/horizons_radec.php');
        if (! $script || ! file_exists($script)) {
            $this->markTestSkipped('Horizons helper script not found');
        }

        $des = '12P';
        $datetime = '2025-11-18 16:08';
        $lon = 4.84457;
        $lat = 49.3447;
        $height = 130;

        // Call helper directly to obtain authoritative RA/Dec
        $cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script) . ' '
            . escapeshellarg($des) . ' ' . escapeshellarg($datetime) . ' '
            . escapeshellarg((string)$lon) . ' ' . escapeshellarg((string)$lat) . ' ' . escapeshellarg((string)$height);

        $out = null;
        $ret = null;
        exec($cmd, $out, $ret);
        if ($ret !== 0) {
            $this->markTestSkipped('Horizons helper failed to produce JSON: ' . implode("\n", $out));
        }

        $helperJson = @json_decode(implode("\n", $out), true);
        if (! is_array($helperJson) || ! isset($helperJson['ra_hours'])) {
            $this->markTestSkipped('Invalid JSON from helper');
        }

        $expected = new EquatorialCoordinates(floatval($helperJson['ra_hours']), floatval($helperJson['dec_deg']));

        // Build Elliptic and ask it to use Horizons
        $ell = new Elliptic();
        $ell->setUseHorizons(true);
        $ell->setHorizonsDesignation($des);

        $date = Carbon::createFromFormat('Y-m-d H:i', $datetime, 'UTC');
        $geo = new GeographicalCoordinates($lon, $lat);

        // Run calculation (the method expects date, geo, epoch, height)
        $ell->calculateEquatorialCoordinates($date, $geo, 2451545.0, $height);

        $got = $ell->getEquatorialCoordinatesToday();

        // Compute angular separation (degrees)
        $sep = $got->angularSeparation($expected)->getCoordinate();

        // Assert small separation (<= 0.02 deg ~72 arcsec)
        $this->assertLessThanOrEqual(0.02, $sep, 'Elliptic Horizons coordinates differ from helper by ' . $sep . ' deg');
    }
}
