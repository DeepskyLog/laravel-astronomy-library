<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Carbon\Carbon;

final class EllipticHorizonsTest extends TestCase
{
    public function testEllipticUsesHorizonsHelperFor12P(): void
    {
        $ell = new Elliptic();
        $ell->setUseHorizons(true);
        $ell->setHorizonsDesignation('12P');

        $date = Carbon::createFromFormat('Y-m-d H:i', '2025-11-18 16:08', 'UTC');
        $geo = new GeographicalCoordinates(4.84457, 49.3447);

        // Calculate (this should call the horizons helper)
        $ell->calculateEquatorialCoordinates($date->copy(), $geo, 130.0);

        $e = $ell->getEquatorialCoordinatesToday();

        $ra = $e->getRA()->getCoordinate();
        $dec = $e->getDeclination()->getCoordinate();

        // Expected authoritative values
        $expectedRa = 16 + 19 / 60 + 36.52 / 3600; // 16.326811111...
        $expectedDec = -36 - 20 / 60 - 0.4 / 3600; // -36.333444444...

        $this->assertLessThan(0.01, abs($ra - $expectedRa), 'RA differs too much');
        $this->assertLessThan(0.05, abs($dec - $expectedDec), 'Dec differs too much');
    }
}
