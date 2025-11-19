
# Getting RA/Dec for a comet (JPL Horizons)

This document shows the supported ways to obtain authoritative apparent Right
Ascension (RA) and Declination (Dec) for a comet using this project. It
describes: (A) calling the bundled Horizons helper, and (B) asking the
library's `Elliptic` class to use JPL Horizons.

Prerequisites

- Network access to query JPL Horizons (required for live integration).
- PHP CLI available on PATH (used to run the helper script).
- The helper script: `scripts/horizons_radec.php` (should exist in the project root).

1) Using the Horizons helper script (quick, direct)

From the project root run:

```bash
php scripts/horizons_radec.php '12P' '2025-11-18 16:08' 4.84457 49.3447 130
```

- Argument 1: Horizons designation or object name (for example: `12P`, `1P`, `103P/Hartley`).
- Argument 2: UTC datetime in `YYYY-MM-DD HH:MM` format.
- Argument 3/4: observer longitude and latitude in decimal degrees (signed).
- Argument 5: observer height in metres.

The helper prints JSON to stdout similar to:

```json
{
  "ra_hours": 16.327372222222223,
  "dec_deg": -36.33230555555556,
  "raw_ra": "16 19 38.54",
  "raw_dec": "-36 19 56.3"
}
```

Fields:

- `ra_hours`: RA in decimal hours (0..24).
- `dec_deg`: Declination in decimal degrees (-90..90).
- `raw_ra`, `raw_dec`: the raw HMS/DMS strings parsed from Horizons (for debugging).

2) Using the library: `Elliptic` (ask the class to call Horizons)

If you want the library to fetch authoritative apparent coordinates from
Horizons at runtime, create an `Elliptic` instance and enable Horizons mode.
Important: call `setHorizonsDesignation(...)` with a valid designation before
calculating coordinates.

Example (PHP):

```php
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Carbon\Carbon;

$ell = new Elliptic();
$ell->setUseHorizons(true);
$ell->setHorizonsDesignation('12P');

$date = Carbon::createFromFormat('Y-m-d H:i', '2025-11-18 16:08', 'UTC');
$geo = new GeographicalCoordinates(4.84457, 49.3447);

$ell->calculateEquatorialCoordinates($date, $geo, 2451545.0, 130.0);

$today = $ell->getEquatorialCoordinatesToday();
echo "RA (hours): " . $today->getRA()->getCoordinate() . "\n";
echo "Dec (deg): " . $today->getDeclination()->getCoordinate() . "\n";
```

Notes and troubleshooting

- Helper not found: when PHPUnit or a different working directory runs tests,
  the library resolves the helper relative to the project root. Ensure
  `scripts/horizons_radec.php` exists and is readable.
- Designation required: `setUseHorizons(true)` requires a prior call to
  `setHorizonsDesignation(...)`. The library avoids accessing uninitialised
  orbital-element properties when Horizons mode is used.
- Ambiguous names: some comets require a specific alias or numeric record id
  to be resolved by Horizons (examples: `103P/Hartley 2` variants). The
  helper implements heuristics to handle index-search results but may still
  need aliases.

Making tests deterministic

- Integration tests that call Horizons require network access and may be
  non-deterministic. Options:
  - Keep them as integration tests and skip on offline CI.
  - Mock the helper (return canned JSON) for unit tests.
  - Use a small alias cache (`scripts/horizons_aliases.json`) to remember
    successful record ids and consult the cache first. This project can
    be extended to write and read this cache.

Developer / debugging commands

Run a single helper query:

```bash
php scripts/horizons_radec.php '12P' '2025-11-18 16:08' 4.84457 49.3447 130
```

Run the helper-based comet integration tests:

```bash
./vendor/bin/phpunit --testdox tests/Unit/CometsHorizonsTest.php
./vendor/bin/phpunit --testdox tests/Unit/EllipticHorizonsIntegrationTest.php
```

If a test fails with an ambiguous Horizons response, inspect debug files
written by the helper in the project `scripts/` directory:

- `scripts/horizons_raw.txt` — full raw response from Horizons.
- `scripts/horizons_block.txt` — extracted $$SOE..$$EOE block (if present).
- `scripts/horizons_resp.json` — last structured JSON output the helper wrote.

If you'd like

- I can add a small alias/cache file (`scripts/horizons_aliases.json`) and
  update the helper to populate it when it resolves a record id successfully.
- I can also add a small example script that queries a list of objects and
  writes a CSV of RA/Dec for batch processing.
