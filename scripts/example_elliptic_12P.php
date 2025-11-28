<?php

/**
 * Example: run the Elliptic target for comet 12P/Pons-Brooks on 2025-11-24.
 *
 * Usage:
 *   php scripts/example_elliptic_12P.php
 *
 * Notes:
 * - This example requests JPL Horizons (requires internet access).
 * - If your machine cannot reach JPL, the script will report the failure.
 */

require __DIR__.'/../vendor/autoload.php';

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Elliptic;

// Date to compute (UTC)
$date = Carbon::create(2025, 11, 24, 0, 0, 0, 'UTC');

// Create the Elliptic target and request JPL Horizons for '12P'
$obj = new Elliptic();
$obj->setHorizonsDesignation('12P');
$obj->setUseHorizons(true);

// Optional: set a geographic location for topocentric corrections (lon, lat in degrees)
$geo = new GeographicalCoordinates(0.0, 0.0);

echo 'Computing 12P/Pons-Brooks ephemeris for '.$date->toIso8601String()." (UTC)\n";

try {
    // calculateEquatorialCoordinates expects the date and optional args: (GeographicalCoordinates, epoch, heightMeters)
    $obj->calculateEquatorialCoordinates($date->copy(), $geo, 0.0);

    $coords = $obj->getEquatorialCoordinatesToday();

    $raHours = $coords->getRA()->getCoordinate();
    $decDeg = $coords->getDeclination()->getCoordinate();

    printf("RA (hours): %.6f\n", $raHours);
    printf("Dec (deg) : %.6f\n", $decDeg);

    // Magnitude: Elliptic->magnitude() returns 99.9 when no photometry is available
    $mag = $obj->magnitude($date->copy());
    if ($mag === 99.9) {
        echo "Magnitude : (not available via H-G for this object)\n";
    } else {
        printf("Magnitude : %.2f\n", $mag);
    }
} catch (\Throwable $e) {
    echo 'Error computing ephemeris: '.$e->getMessage()."\n";
    echo "If you are offline or the Horizons helper fails, try running Horizons helper directly:\n";
    echo "  php scripts/horizons_radec.php '12P' '2025-11-24 00:00' 0.0 0.0 0\n";
}

echo "Finished.\n";
