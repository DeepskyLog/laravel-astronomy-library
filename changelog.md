# Changelog

All notable changes to `laravel-astronomy-library` will be documented in this file.

## Version 1.2

- Added methods to calculate the dynamical time.

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

  - This means adding a table to the database, and a cronjob to update the table every day / week / month.

## Version 1.0

### Added

- Methods to convert from Carbon dates to julian date
- Methods to convert between NELM, SQM and bortle
