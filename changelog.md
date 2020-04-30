# Changelog

All notable changes to `laravel-astronomy-library` will be documented in this file.

## Version 2.1

### Changed

- GeographicalCoordinates class now inherits from the abstract Coordinates class.
- Moved GeographicalCoordinates to deepskylog\AstronomyLibrary\Coordinates.

### Added

- Added abstract Coordinates class.
- Added EquatorialCoordinates, EclipticalCoordinates, and GalacticCoordinates classes.

## Version 2.0.1

- Bump minimum php version to 7.4.
- Bump minimum laravel version to 7.0.

## Version 2.0

### Changed

- The constructor of AstronomyLibrary now needs the geographical coordinates as parameter.

### Added

- Added methods to calculate the dynamical time.
- Added methods to calculate the mean and apparent siderial time at the given location.
- Added methods to calculate the nutation for a given date.
- Added GeographicalCoordinates class.

## Version 1.1

### Fixed

- Fixed conversion from SQM to NELM and back.

### Added

- More [documentation](docs/docs.md) on the mathematical background of the used formulae.
- Methods to calculate dynamical time.
  - The list of delta t values from 1620 to 2011 is taken from the webpage of [R.H. van Gent](https://www.staff.science.uu.nl/~gent0113/deltat/deltat.htm)
  - The values from 2011 onward are taken from the VVS mailing list, provided by Jean Meeus.
  - This is the graph with the delta t values from 1620 to today:
![Delta t values](docs/deltat.png "Delta t values")
  - A new table delta_t is added to the database, and a cronjob to update the table every day / week / month is added to the scheduler.

## Version 1.0

### Added

- Methods to convert from Carbon dates to julian date
- Methods to convert between NELM, SQM and bortle
