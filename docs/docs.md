# Laravel-astronomy-library documentation

Most of the calculations are from the formulae in Astronomical Algorithms by Jean Meeus.

## Time

### Julian day

The standard calculations in php to calculate the julian day do not take into account the time of the day. Moreover, the php calculation do not take care of the fact that the julian day starts at noon.  To overcome these problems, we reimplemented the calculations of the julian day using the formulae in Astronomical Algorithms by Jean Meeus.

### Delta T

- The list of Delta T values from 1620 to 2011 is taken from the webpage of [R.H. van Gent](https://www.staff.science.uu.nl/~gent0113/deltat/deltat.htm)
- The values from 2011 onward are taken from the VVS mailing list, provided by Jean Meeus.
- The values can be calculated from IERS.
  - First, get the [TAI-UTC value](ftp://cddis.gsfc.nasa.gov/pub/products/iers/tai-utc.dat).
  - Get [(UT1 - UTC)](https://www.iers.org/IERS/EN/DataProducts/EarthOrientationData/eop.html) from the IERS website (bulletin B).
  - Delta T is 32.184 + (TAI - UTC) - (UT1 - UTC) / 1000.
- This is the graph with the Delta T values from 1620 to today:
![Delta t values](deltat.png "Delta T values")
- The formulae for the years that are not tabulated, are taken from the [NASA Eclipse Website](https://eclipse.gsfc.nasa.gov/SEcat5/deltatpoly.html)

### Nutation

- The array that is returned from the calculation of the nutation (Time::nutation(jd)) contains the following information:
  - nutation in Longitude
  - nutation in Obliquity
  - mean Obliquity
  - true Obliquity

## Magnitude

### Conversion between NELM, SQM and Bortle Scale

The formulae to convert between NELM and SQM are taken from the Telescope Limiting Magnitude article by [Schaefer, 1990](http://adsbit.harvard.edu/cgi-bin/nph-iarticle_query?bibcode=1990PASP..102..212S).

### Coordinates

- There are classes for Ecliptical, Equatorial, Galactic, Horizontal and Geographical Coordinates.
- The formulae to do the conversions between Coordinate systems are from Astronomical Algorithms by Jean Meeus.

### Contrast Reserve

- The contrast reserve of an object tells how easy it is to detect an object with a certain instrument from a certain sky.  The calculation of the contrast reserve does not take into account brighter parts of the object.  It is possible that the contrast reserve clearly tells that a galaxy is not visible, but that the center of the galaxy is really bright and is visible after all.  The conclusion is that the contrast reserve is an indication of how easy an object is to see, but even if the contrast reserve points out that the object should not be visible, you should always try to see if the brighter parts of the object is not visible.
- When a list of possible magnifications is given, the best magnification to detect the object can be given.  This does not mean that this magnification is the best magnification to see the most details.

| Contrast reserve interval | Constrast reserve interval | Meaning |
|----| ----| ---|
| | < -0.2 | The object is not visible |
| > -0.2 | < 0.1 | The detection of the object is questionable |
| 0.10 | < 0.35 | The detection of the object is difficult |
| 0.35 | < 0.5 | The detection of the object is quite difficult |
| 0.50 | < 1.0 | The detection of the object is easy |
| 1.00 |  | The detection of the object is very easy |

## Orbital elements

The orbital elements of the comets and the asteroids are downloaded from the [JPL site](https://ssd.jpl.nasa.gov/?sb_elem).
