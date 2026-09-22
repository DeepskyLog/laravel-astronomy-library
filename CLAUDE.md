# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`deepskylog/laravel-astronomylibrary` is a Composer package (not a standalone app) that adds astronomical
calculation utilities to a Laravel host application: time/Julian day/delta-T, coordinate system conversions,
magnitude conversions, and target ephemeris calculations (Sun, Moon, planets, comets, asteroids). Most formulae
come from *Astronomical Algorithms* by Jean Meeus — see `docs/docs.md` for the mathematical background and
`readme.md` for the full usage/API reference with code examples.

## Commands

Run tests (needs a full Laravel skeleton for the Testing base class, see below):

```bash
vendor/bin/phpunit                       # whole suite (phpunit.xml -> tests/)
vendor/bin/phpunit tests/Unit/TimeTest.php
vendor/bin/phpunit --filter testMethodName tests/Unit/TargetTest.php
```

Install dependencies:

```bash
composer install
```

There is no build step, linter invocation, or artisan app in this repo itself — StyleCI (`.styleci.yml`, laravel
preset) runs on pushes, and Travis (`.travis.yml`) runs `vendor/bin/phpunit`.

## Test architecture

- Tests extend `deepskylog\AstronomyLibrary\Testing\BaseTestCase`, which boots a real Laravel application from
  `$appPath` (each test class sets `protected $appPath = __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php'`)
  and registers `AstronomyLibraryServiceProvider`. This means `laravel/laravel` must be present under `vendor/`
  (it's a `require-dev` dependency) — tests genuinely run against a booted Laravel app, not a mocked container.
- Tests that hit JPL Horizons or SBDB live services are named `*HorizonsTest.php` / `*HorizonsIntegrationTest.php`
  / `*IntegrationTest.php` — expect these to need network access and to be slower/flakier than pure-calculation
  unit tests.
- `scripts/` contains standalone debug/dry-run PHP scripts (not part of the test suite) used to manually exercise
  Horizons/SBDB/aerith.net fetches and comet photometry parsing during development — reach for them when
  debugging those integrations rather than writing new throwaway scripts.

## Code architecture

### Autoloading quirk

`composer.json` uses **PSR-0** autoloading (`"deepskylog\\AstronomyLibrary\\": "src/"`), and the actual source
lives nested at `src/deepskylog/AstronomyLibrary/...` (the namespace path is repeated inside `src/`). Keep new
classes under that same nested path matching their namespace, not directly under `src/`.

### Namespace map (all under `deepskylog\AstronomyLibrary\`)

- **`Coordinates\`** — `Coordinate` is the generic single-value base (used for angles/hour angles/generic
  measurements with print/format helpers). `EquatorialCoordinates`, `EclipticalCoordinates`,
  `HorizontalCoordinates`, `GalacticCoordinates`, `GeographicalCoordinates`, `RectangularCoordinates` each wrap a
  coordinate pair and know how to convert to the others (e.g. `EquatorialCoordinates::convertToEcliptical()`,
  `convertToHorizontal()`, `convertToGalactic()`), plus precession, angular separation, straight-line/deviation
  checks, and constellation/atlas-page lookups.
- **`Targets\`** — `Target` is the base class for anything observable: it holds equatorial coordinates for
  yesterday/today/tomorrow and computes rise/transit/set, max height, best time to observe, contrast reserve, and
  altitude/magnitude graphs (rendered as PNG and returned as HTML `<img>` via `yearMagnitudeGraph()` /
  `yearDiameterGraph()` / `altitudeGraph()`). Specializations:
  - `Planet` (abstract) extends `Target`, and each of `Mercury`/`Venus`/`Earth`/`Mars`/`Jupiter`/`Saturn`/
    `Uranus`/`Neptune` extends `Planet` with its own VSOP87-derived orbital-element/coordinate tables (these
    files are large — thousands of lines of numeric series data).
  - `Sun` and `Moon` extend `Target` directly with their own geometric/apparent-position calculations.
  - `Elliptic`, `Parabolic`, `NearParabolic` extend `Target` for comets/asteroids on elliptic, parabolic, or
    near-parabolic orbits respectively (`setOrbitalElements(...)`, `calculateEquatorialCoordinates()`,
    magnitude models via `setHG()` for the IAU H-G asteroid system or `setCometParams()` for the comet
    H + 5·log(Δ) + n·log(r) model).
- **`Models\`** — Eloquent models backing package-published migrations: `DeltaT`, `CometsOrbitalElements`,
  `AsteroidsOrbitalElements`, `ConstellationBoundaries`.
- **`Imports\`** — `maatwebsite/excel` import classes (`DeltaTImport`, `ConstellationBoundariesImport`) used to
  seed those tables from the CSV/XLSX files in `data/`.
- **`Commands\`** — Artisan commands registered by the service provider:
  - `astronomy:updateDeltat` (`UpdateDeltaTTable`) — scheduled quarterly.
  - `astronomy:updateOrbitalElements` (`UpdateOrbitalElements`) — streams comet/asteroid orbital elements from
    JPL line-by-line for performance; scheduled weekly (Monday 04:30).
  - `astronomy:updateCometPhotometry` (`UpdateCometPhotometry`) — scrapes comet H/n/phase photometry from
    aerith.net with an SBDB fallback; supports `--target=` to limit to one object and honors `AERITH_VERIFY` /
    `AERITH_CA_BUNDLE` env vars for TLS verification of that fetch.
- **`Time`** and **`Magnitude`** are static-method utility classes (Julian day conversions, delta-T, sidereal
  time, nutation; NELM/SQM/Bortle scale conversions) with no per-instance state.
- **`AstronomyLibrary`** is the facade-backed entry point (`Facades\astronomyLibrary`) that wraps a date +
  `GeographicalCoordinates` pair and exposes the time/coordinate-conversion convenience methods shown in
  `readme.md`.

### Service provider wiring

`AstronomyLibraryServiceProvider` publishes migration stubs from `src/database/migrations/*.stub` plus CSV seed
data from `data/` (tag: `migrations`), registers the three Artisan commands, and — notably — hooks into the
host app's `Illuminate\Console\Scheduling\Schedule` binding via `$this->app->booted(...)` so the package's own
`Console\Kernel::schedule()` (delta-T + orbital-element update jobs) runs automatically as part of the host
app's `php artisan schedule:run`, without the host needing to register anything manually.

### Migrations are additive stubs, not auto-run

There's both a create-table stub and a separate ALTER stub (`add_photometry_to_comets_orbital_elements_table`)
for adding optional photometry columns (`H`, `n`, `phase_coeff`, `n_pre`, `n_post`) to existing installs. When
changing the `comets_orbital_elements` schema, prefer adding a new ALTER stub over editing the create-table stub,
so existing installations can pick up the change via `vendor:publish` + `migrate` without data loss.
