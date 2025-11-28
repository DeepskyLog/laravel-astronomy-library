# Changelog

All notable changes to `laravel-astronomy-library` will be documented in this file.

## Version 6.7.5

Changed:

- Improved aerith/SBDB fallback behavior and photometry persistence (already introduced earlier, documented here for visibility):
  - The command still attempts aerith.net year-specific pages and directory candidates, and falls back to JPL SBDB when aerith scraping yields no photometry.
  - Photometry values discovered (absolute magnitude `H`, slope `n`, and phase coefficient/G when available) are persisted to the `comets_orbital_elements` model.

Changed (Model):

- `CometsOrbitalElements` (`src/deepskylog/AstronomyLibrary/Models/CometsOrbitalElements.php`):
  - Exposes photometry fields in `$fillable`: `H`, `n`, `phase_coeff`, `n_pre`, `n_post` so the `astronomy:updateCometPhotometry` command can persist discovered values.
  - Uses `name` as the primary key and disables timestamps (no behavior change, documented for clarity).

## Version 6.7.4

Changed:

- Improved the `astronomy:updateCometPhotometry` artisan command (`src/deepskylog/AstronomyLibrary/Commands/UpdateCometPhotometry.php`):
  - Add configurable HTTP client verification via environment variables: `AERITH_VERIFY` (accepts `true`/`false` or a path to a CA bundle) and `AERITH_CA_BUNDLE` to allow running in environments with custom CA bundles or where verification needs disabling for troubleshooting.
  - Use a short request timeout and a browser-like `User-Agent` to reduce simple blocking by `aerith.net`.
  - Expand and harden aerith.net lookup heuristics: generate multiple candidate URLs (name-based slugs, zero-padded periodic designations such as `0103P`, plain designation directories, and year-specific pages), try directory pages and prefer newest year pages when available, and fall back gracefully to directory/index pages.
  - More robust HTML parsing for photometry: scan page text nodes for `H = ...`, `n = ...` and phase coefficient patterns and persist discovered values to the `comets_orbital_elements` model (`H`, `n`, `phase_coeff`). Improved logging when photometry is found or not.
  - Improved error handling around HTTP requests (404 handling, request exception logging and continued attempts against other candidate URLs).
  - Added an SBDB fallback helper method (`sbdbFallback`) to query JPL's SBDB API for an `H` value when aerith.net scraping does not yield photometry (method included for fallback resolution).

Fixed / Notes:

- The photometry updater is now more resilient to variation in aerith.net page layout and index pages; environments with custom CA bundles or strict SSL settings should set `AERITH_VERIFY`/`AERITH_CA_BUNDLE` appropriately.

## Version 6.7.3

Fixed:

- Renamed the photometry migration class in the publishable stub from `AddPhotometryToCometsTable` to `AddPhotometryToCometsOrbitalElementsTable` to avoid duplicate-class collisions when multiple published migration files exist in host applications. Re-publish migrations to pick up the updated class name.

## Version 6.7.2

Fixed:

- Removed a duplicated PHP block from the photometry migration stub so published migrations no longer contain two `<?php` tags (preventing parse errors when host apps run `php artisan migrate`). The corrected stub is `src/database/migrations/add_photometry_to_comets_orbital_elements_table.php.stub` and contains a single idempotent migration class.
- Ensured the package publishes the photometry migration stub (service provider updated) and verified the migration is idempotent and rollback-safe.

## Version 6.7.1

Changed:

- Hardened package migrations and stubs to be idempotent: create-table stubs and data-import migrations now check `Schema::hasTable`/`Schema::hasColumn` before creating/importing to avoid "table already exists" errors when published into projects that already have the tables.
- Fixed `CreateAsteroidsOrbitalElementsTable` `down()` method which previously dropped the wrong table name.
- Added a publishable photometry migration stub: `src/database/migrations/add_photometry_to_comets_orbital_elements_table.php.stub` and updated the service provider to publish it so host apps will receive the migration via `vendor:publish`.
- Made the photometry migration idempotent (guards around each added column) and made rollback safe by dropping only existing columns.

## Version 6.7.0

Added:

- Prefer DASTCOM element files for small-body orbital elements when available to reduce ambiguous designation lookups.
- Fallback resolution using SB-CAP (small-body catalog) when DASTCOM lookups fail.
- `--used-command` debug output exposed by the Horizons helper (`scripts/horizons_radec.php`) and included in JSON responses to aid troubleshooting.
- Comet photometry support:
  - Migration stub: `src/database/migrations/add_photometry_to_comets_orbital_elements_table.php.stub`.
- Artisan command: `astronomy:updateCometPhotometry` (`src/deepskylog/AstronomyLibrary/Commands/UpdateCometPhotometry.php`) to fetch photometric parameters (attempts Seiichi Yoshida / `aerith.net`).
- Command registration: `AstronomyLibraryServiceProvider.php` and `Console/Kernel.php` register the new command.
- Model: `src/deepskylog/AstronomyLibrary/Models/CometsOrbitalElements.php` exposes and persists photometry fields when available.
- README/docs: migration instructions and worked examples for comet photometry and magnitude calculations were added to `readme.md`.
- Tests: unit tests covering comet photometry ingestion and Horizons-based coordinate checks were added/updated (see `tests/Unit/*Comets*`, `CometsHorizonsTest`, `PlanetHorizonsDE440Test`).

Changed:

- `scripts/horizons_radec.php`: prefer JSON output, extract `$$SOE`..`$$EOE` blocks reliably, retry record-id resolution, prefer DASTCOM records and try SB-CAP fallback when needed, and surface the `used_command` used for queries.
- Targets: photometric magnitude models and API updates implemented in `src/deepskylog/AstronomyLibrary/Targets/Elliptic.php`, `src/deepskylog/AstronomyLibrary/Targets/Parabolic.php` and `src/deepskylog/AstronomyLibrary/Targets/NearParabolic.php` (asteroid H–G model, comet H + 5 log10(delta) + n log10(r) with optional phase term and pre/post-perihelion exponents).
- Planetary Horizons: optional JPL/Horizons ephemeris lookup added to `src/deepskylog/AstronomyLibrary/Targets/Planet.php` (uses `scripts/horizons_radec.php`).
- Tests & fixtures: new DE440-based Horizons tests (`tests/Unit/PlanetHorizonsDE440Test.php`, `tests/Unit/PrintPlanetCoordsTest.php`) and updated fixtures in `scripts/` (including `scripts/horizons_resp_Jupiter.json`, `scripts/horizons_resp_Mars.json`, updated `scripts/horizons_resp.json`, `scripts/horizons_raw.txt`, `scripts/horizons_block.txt`).
- Commands / scheduling: `UpdateOrbitalElements` and related scheduling notes updated so orbital-element and photometry updates are runnable (weekly scheduling noted in `readme.md`).

Fixed:

- Hardened parsing and record-resolution heuristics for Horizons responses to avoid sporadic failures when Horizons returns index pages or ambiguous matches.
- Comet magnitude computation: hardened handling for missing photometry (falls back to sensible defaults) and correct application of pre-/post-perihelion `n` exponents and optional phase coefficients when present.
- Tests: relaxed numeric assertions where appropriate and added tests that validate the new Horizons integration and photometry ingestion.

## Version 6.6.0

Added:

- `scripts/horizons_radec.php` — a small CLI helper that queries the JPL Horizons ephemeris service and returns structured JSON (apparent RA/Dec) for observer-based ephemerides. The helper writes optional debug artifacts to `scripts/horizons_raw.txt`, `scripts/horizons_block.txt` and `scripts/horizons_resp.json` to aid troubleshooting.
- PHPUnit integration tests that exercise the Horizons helper and library integration (unit/integration tests under `tests/Unit/*Horizons*`).
- `docs/getting_radec.md` — documentation covering how to obtain authoritative RA/Dec for small bodies using the Horizons helper and how to enable Horizons mode in `Elliptic` targets.

Changed:

- `Elliptic` target: added a Horizons integration path — when enabled the library will invoke the helper to obtain authoritative apparent RA/Dec for observer-based ephemerides instead of using only internal propagation. Also added setters to enable Horizons mode and to provide an explicit Horizons designation.
- Improved canonicalisation of orbital elements in `Elliptic::setOrbitalElements()` (angle wrapping and inclination handling) to reduce ambiguity when propagating elements.

Fixed:

- Hardened parsing and record-resolution heuristics for Horizons responses (helper now prefers JSON output, extracts $$SOE..$$EOE blocks robustly, and retries record-id resolution when an index search is returned).

Notes:

- A lightweight cache/alias mechanism for Horizons id resolution is planned to make repetitive integration tests more deterministic (not yet added in this release).

## Version 6.5.1

Changed:

- Increased diameter precision for planets by removing final rounding in `magnitude()` (reduces 0.1 quantization in graphs). Affected: Jupiter, Mercury, Venus, Mars, Neptune, Uranus, Saturn.

## Version 6.5

Added:

- `Target::yearDiameterGraph(GeographicalCoordinates $geo_coords, Carbon $date, bool $debug = false)` — generate a year-long plot of the planet's apparent angular diameter (arcseconds).

Changed:

- `Target::yearMagnitudeGraph(..., $debug = true)` now emits debug logging for skipped samples and exceptions (rendered into the placeholder/debug image when `debug` is true).
- Increased magnitude precision for several planet magnitude implementations by removing final rounding in `magnitude()` (reduces 0.1-mag quantization in graphs). Affected: Jupiter, Mercury, Venus, Mars, Neptune, Uranus, Saturn.

## Version 6.4

Added:

- Targets can now generate a graph of the maximum altitude during a whole year

Fixed:

- A lot of fixes in the calculation of the maximum altitude during the night and best time to observe a target.
- the altitude graph now shows the correct colors for nautical twilight.

## Version 6.3.1

Fixed:

- Fixed syntax error

## Version 6.3

Added:

- Added moon elevation to the altitude graph.
- Only show the elevation graph when the target is above the horizon.

Changed:

- Improved scheduling of package commands.

## Version 6.2.4

Changed:

- Allow laravel 12.

## Version 6.2.3

Changed:

- Added delta t value for 2025

## Version 6.2.2

Fixed:

- Fixed problem calculating planet coordinates
- Added example to calculate the coordinates of Venus

## Version 6.2.1

Fixed:

- AstronomyLibrary crashed when using decimal seconds (for example when running Carbon::now())
- Updated to the newest version of the needed libraries

## Version 6.2

Changed:

- Fix equation of time for March 21 and 22.
- Fix equation of time for March 21 and 22.
- Return equation of time as float and not as CarbonInterval (which fails for negative values).

## Version 6.1.2

Changed:

- Added delta t value for 2024
- Update to use laravel 11

## Version 6.1.1

Changed:

- Added delta t value for 2023
- Update to use laravel 10

## Version 6.1

Changed:

- Added delta t value for 2022
- Update to use laravel 9

## Version 6.0

Added:

- Methods and classes to download the orbital elements of comets and asteroids.

Changed:

- Renamed the console command

```bash
php artisan deltat:update
```

to

```bash
php artisan astronomy:updateDeltat
```

## Version 5.6

Added:

- Methods to calculate the diameter of the Sun, Moon and planets.

Changed:

- Corrected calculation of coordinates of Mercury.

## Version 5.5

Added:

- Methods to calculate the next new moon (newMoonDate), the next first quarter (firstQuarterMoonDate), the next last quarter (lastQuarterMoonDate) and the next full moon (fullMoonDate).

## Version 5.4.3

Changed:

- Fix error in the illumination of the moon.

## Version 5.4.2

Changed:

- Use higher precision for the moon phase ratio and illumination of the moon.

## Version 5.4.1

Added:

- Added the moon phase ratio.

## Version 5.4

Added:

- Added method to calculate the illumination of the moon.

## Version 5.3

Added:

- Added methods to calculate the coordinates and the distance of the moon.

## Version 5.2

Changed:

- Take into account the ring of Saturn to calculate the magnitude of the planet.

## Version 5.1

Added:

- Method to calculate the illuminated fraction of a planet.
- Method to calculate the magnitude of a planet.  The magnitude of Saturn is fainter the real value, because the orientation of the ring is not yet taken into account.  This correction for this will follow in one of the next versions of this library.

## Version 5.0

### Backwards incompatible changes

- The method calculateEquatorialCoordinates on planets now need three parameters, because the effect of parallax is taken into account.  In stead of only the date, also the geographical coordinates and height of the location are needed as parameter.
- The method calculateApparentEquatorialCoordinates($date) can be used if the corrections for hte parallax are not needed.

Added:

- Height of the location in AstronomyLibrary
- earthsGlobe method for GeographicalCoordinates to calculate rho sin phi accent and rho cas phi accent
- Calculation of parallax for equatorial coordinates

## Version 4.26.2

Changed:

- Added delta t value for 2021

## Version 4.26

Changed:

- AstronomyLibrary now also keeps deltaT, so that we don't have to recalculate or read it from the database again and again.
- The calculation of deltaT will now also work when no database is configured.

## Version 4.25

Added:

- Methods to calculate the date of the ascending and descending node of targets in ellipical and parabolic orbits.

## Version 4.24

Added:

- Calculation of aphelion and perihelion date for the planets.

## Version 4.23.1

Fixed:

- Fix calculation of delta T in the beginning of the year if the new value is not yet available in the database.

## Version 4.23

Added:

- Calculation of inferior / superior conjunction for inner planets
- Opposition / conjuntion of outer planets
- Greatest eastern and western elongation of inner planets.

## Version 4.22

Added:

- Added NearParabolic class, describing an object moving in a near-parabolic orbit. Added method to calculate the coordinates.

## Version 4.21

Added:

- Added Parabolic class, describing an object moving in a parabolic orbit. Added method to calculate the coordinates.

## Version 4.20

Added:

- Added methods to calculate the contrast reserve and the magnification of an object. The contrast reserve tells how easy it is to detect an object.
- If the contrast difference is < 0, the object is not visible, contrast difference < -0.2 : Not visible, -0.2 < contrast diff < 0.1 : questionable, 0.10 < contrast diff < 0.35 : Difficult, 0.35 < contrast diff < 0.5 : Quite difficult to see, 0.50 < contr diff < 1.0 : Easy to see, 1.00 < contrast diff : Very easy to see

## Version 4.19.1

Changed:

- Obliquity is not needed as parameter in the calculateEquatorialCoordinates method of Planet and Elliptic

## Version 4.19

Added:

- Added methods to calculate the Equatorial coordinates of the planets for a given date.
- Added Elliptic class, describing an object moving in an elliptic orbit. An add method to calculate the coordinates.

## Version 4.18

Added:

- Added methods to calculate the Heliocentric coordinates of the planets for a given date.

## Version 4.17

Added:

- Added classes for the planets:
  - Mercury, Venus, Earth, Mars, Jupiter, Saturn, Uranus, Neptune
- Added methods to calculate the mean orbital parameters of the planets for a given date.

## Version 4.16

Added:

- Added method to calculate the eccentric anomaly using the equation of Kepler
  - Target::eccentricAnomaly()

## Version 4.15

Added:

- Added method to calculate the ephemeris for physical observations of the sun
  - Sun::getPhysicalEphemeris()

## Version 4.14

Added:

- Added method to calculate the equation of time
- Added methods to print the coordinates without seconds:
  - convertToShortHours()
  - convertToShortDegrees()

## Version 4.13

Added:

- Added methods to get the start of the seasons:
  - Time::getWinter()
  - Time::getSpring()
  - Time::getSummer()
  - Time::getAutumn()

## Version 4.12

Added:

- Add method to calculate the rectangular coordinates of the sun
- Add RectangularCoordinates class

## Version 4.11

Added:

- Add method to calculate the atlas page corresponding to equatorial coordinates.

## Version 4.10

Added:

- Add method to calculate the constellation when the coordinates are given.  The constellation is returned as 3 characters (Latin name).  The migration should be re-exported and run to be able to run this method.

## Version 4.9.3

Changed:

- Fix the calculation of the altitude graph if the equatorial coordinates of yesterday and tomorrow are also given.

## Version 4.9.2

Changed:

- Add the equatorial coordinates of yesterday and tomorrow to the target (sun).

## Version 4.9.1

Changed:

- Use \Carbon\Carbon everywhere in the code

## Version 4.9

Added:

- Add methods to calculate the equatorial coordinates of the sun.

## Version 4.8

Added:

- Add methods to calculate the apparent place of a star, using the Ron-Vondrák expression. The calculations take into account the perturbations caused by the planets, the precession and the nutation.

## Version 4.7

Added:

- Add precession method for EquatorialCoordinates to calculate the precession for a given date with low accuracy.  The proper motion of the star is taken into account for the calculation of the precession.
- Add precessionHighAccuracy for EquatorialCoordinates method to calculate the precession for a given date with high accuracy.  The proper motion of the star is taken into account for the calculation of the precession.
- Add precessionHighAccuracy for EclipticalCoordinates method to calculate the precession for a given date with high accuracy.  The proper motion of the star is not taken into account for the calculation of the precession.

Changed:

- The constructor of the EquatorialCoordinates class now also takes the epoch of the coordinate as argument.  If the epoch is not given, the standard epoch of 2000.0 is taken.
- The constructor of the EquatorialCoordinates class now also takes the proper motion (in RA and in dec) as arguments.  If the proper motion is not given, the value of 0.0 is taken.

## Version 4.6

Added:

- Added methods to calculate the smallest circle containing three celestial bodies.

## Version 4.5

Added:

- Added methods to check if three bodies are in a straight line and to calculate the deviation from a straight line.

## Version 4.4

Added:

- Added methods to calculate the angular separation between two objects.

## Version 4.3

Added:

- Added methods to calculate the refraction for given horizontal coordinates.

## Version 4.2

Added:

- Added view with the length of the night at a given location.
![Length of night plot](./docs/Night.png "Length of night")

## Version 4.1

Added:

- Added view with the altitude of the target during the night.
![Altitude plot](./docs/Altitude.png "Altitude plot")

## Version 4.0.1

Changed:

- Corrected convertToDegrees method on Coordinates to return h m s, instead of h ' ".
- Added getCoordinates method on Target.

## Version 4.0

Changed:

- Removed abstract Coordinates class.
- Added Coordinate class and reworked all Coordinate Classes to use this new class.

Added:

- Calculation of rising, transit and setting for targets.
- Calculation of best time to observe a target.
- Calculation of highest altitude of a target.
- Added classes for Targets, Moon, Sun, and Planet.

## Version 3.1

Added:

- Calculation of parallactic angle.

## Version 3.0

Changed:

- GeographicalCoordinates class now inherits from the abstract Coordinates class.
- Moved GeographicalCoordinates to deepskylog\AstronomyLibrary\Coordinates.
- The method apparentSiderialTime of the Time class can take an extra parameter nutation.

Added:

- Added abstract Coordinates class.
- Added EquatorialCoordinates, EclipticalCoordinates, Horizontal and GalacticCoordinates classes.
- Added conversion between the Coordinates classes.

## Version 2.0.1

- Bump minimum php version to 7.4.
- Bump minimum laravel version to 7.0.

## Version 2.0

Changed:

- The constructor of AstronomyLibrary now needs the geographical coordinates as parameter.

Added:

- Added methods to calculate the dynamical time.
- Added methods to calculate the mean and apparent siderial time at the given location.
- Added methods to calculate the nutation for a given date.
- Added GeographicalCoordinates class.

## Version 1.1

Fixed:

- Fixed conversion from SQM to NELM and back.

Added:

- More [documentation](docs/docs.md) on the mathematical background of the used formulae.
- Methods to calculate dynamical time.
  - The list of delta t values from 1620 to 2011 is taken from the webpage of [R.H. van Gent](https://www.staff.science.uu.nl/~gent0113/deltat/deltat.htm)
  - The values from 2011 onward are taken from the VVS mailing list, provided by Jean Meeus.
  - This is the graph with the delta t values from 1620 to today:
![Delta t values](docs/deltat.png "Delta t values")
  - A new table delta_t is added to the database, and a cronjob to update the table every day / week / month is added to the scheduler.

## Version 1.0

Added:

- Methods to convert from Carbon dates to julian date
- Methods to convert between NELM, SQM and bortle
