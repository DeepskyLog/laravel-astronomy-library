# laravel-astronomylibrary

> Astronomical calculations for php / laravel

[![Logo](public/img/logo2.png)](https://www.deepskylog.org/)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

Take a look at [contributing.md](contributing.md) if you are interesting in helping out.
The laravel-astronomylibrary is part of [DeepskyLog](https://www.deepskylog.org). If you are interested in helping with the development of DeepskyLog, see the [documentation](https://github.com/DeepskyLog/DeepskyLog/blob/laravel/README.md).

## Installation

Via Composer

``` bash
composer require deepskylog/laravel-astronomy-library
```

## Usage

```php
<?php
// Use the factory to create a AstronomyLibrary instance
$astrolib = new AstronomyLibrary($carbonDate);
```

### Time methods

```php
// Get the date of the AstronomyLibrary instance
$date = $astrolib->getDate();

// Set a new date to the AstronomyLibrary instance
$astrolib->setDate($carbonDate);

// Get the julian day of the AstronomyLibrary instance
$jd = $astrolib->getJd();

// Set the julian day of the AstronomyLibrary instance. Also update the carbon date.
$astrolib->setJd($jd);
```

### Static Time methods

```php
// Convert from Carbon date to Julian day
$jd = Time::getJd($carbonDate);

// Convert from Julian day to Carbon date
$carbonDate = Time::fromJd($jd);
```

## Magnitude methods

### Static magnitude methods

```php
// Convert from Naked Eye Limiting Magnitude to SQM value
$sqm = Magnitude::nelmToSqm($sqm, $fstOffset);

// Convert from Naked Eye Limiting Magnitude to bortle scale
$bortle = Magnitude::nelmToBortle($sqm);

// Convert from SQM value to Naked Eye Limiting Magnitude
$nelm = Magnitude::sqmToNelm($sqm, $fstOffset);

// Convert from SQM value to bortle scale
$bortle = Magnitude::sqmToBortle($sqm);

// Convert from bortle scale to Naked Eye Limiting Magnitude
$nelm = Magnitude::bortleToNelm($bortle, $fstOffset);

// Convert from bortle scale to SQM value
$sqm = Magnitude::bortleToNelm($bortle, $fstOffset);
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
phpunit
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email developers@deepskylog.be instead of using the issue tracker.

## Credits

- [The DeepskyLog Team][link-author]
- [All Contributors][link-contributors]

## License

GPLv3. Please see the [license file](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/deepskylog/laravel-astronomy-library.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/deepskylog/laravel-astronomy-library.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/deepskylog/laravel-astronomy-library/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/255550499/shield

[link-packagist]: https://packagist.org/packages/deepskylog/laravel-astronomy-library
[link-downloads]: https://packagist.org/packages/deepskylog/laravel-astronomy-library
[link-travis]: https://travis-ci.org/deepskylog/laravel-astronomy-library
[link-styleci]: https://styleci.io/repos/255550499
[link-author]: https://github.com/DeepskyLog
[link-contributors]: ../../contributors
