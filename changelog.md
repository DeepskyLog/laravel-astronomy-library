# Changelog

All notable changes to `laravel-astronomy-library` will be documented in this file.

## Version 4.3

### Added

- Added methods to calculate the refraction for given horizontal coordinates.

## Version 4.2

### Added

- Added view with the length of the night at a given location.
![Length of night plot](./docs/Night.png "Length of night")

## Version 4.1

### Added

- Added view with the altitude of the target during the night.
![Altitude plot](./docs/Altitude.png "Altitude plot")

## Version 4.0.1

### Changed

- Corrected convertToDegrees method on Coordinates to return h m s, instead of h ' ".
- Added getCoordinates method on Target.

## Version 4.0

### Changed

- Removed abstract Coordinates class.
- Added Coordinate class and reworked all Coordinate Classes to use this new class.
  
### Added

- Calculation of rising, transit and setting for targets.
- Calculation of best time to observe a target.
- Calculation of highest altitude of a target.
- Added classes for Targets, Moon, Sun, and Planet.

## Version 3.1

### Added

- Calculation of parallactic angle.

## Version 3.0

### Changed

- GeographicalCoordinates class now inherits from the abstract Coordinates class.
- Moved GeographicalCoordinates to deepskylog\AstronomyLibrary\Coordinates.
- The method apparentSiderialTime of the Time class can take an extra parameter nutation.

### Added

- Added abstract Coordinates class.
- Added EquatorialCoordinates, EclipticalCoordinates, Horizontal and GalacticCoordinates classes.
- Added conversion between the Coordinates classes.

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
