<?php

/**
 * The target class describing a planet.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace deepskylog\AstronomyLibrary\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

/**
 * The target class describing a planet.
 *
 * PHP Version 7
 *
 * @category Target
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Planet extends Target
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->setH0(-0.5667);
    }

    /**
     * Calculates the apparent equatorial coordinates of the planet.
     *
     * @param Carbon $date      The date for which to calculate the coordinates
     *
     * See chapter 33 of Astronomical Algorithms
     */
    public function calculateApparentEquatorialCoordinates(Carbon $date): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateApparentEquatorialCoordinates($date)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateApparentEquatorialCoordinates($date->addDay())
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateApparentEquatorialCoordinates($date->subDays(2))
        );
    }

    /**
     * Calculates the topocentric equatorial coordinates of the planet.
     *
     * @param Carbon                  $date       The date for which to calculate the coordinates
     * @param GeographicalCoordinates $geo_coords The geographical coordinates
     * @param float                   $height     The height of the location
     *
     * See chapter 40 of Astronomical Algorithms
     */
    public function calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): void
    {
        $this->setEquatorialCoordinatesToday(
            $this->_calculateEquatorialCoordinates($date, $geo_coords, $height)
        );
        $this->setEquatorialCoordinatesTomorrow(
            $this->_calculateEquatorialCoordinates($date->addDay(), $geo_coords, $height)
        );
        $this->setEquatorialCoordinatesYesterday(
            $this->_calculateEquatorialCoordinates($date->subDays(2), $geo_coords, $height)
        );
    }

    private function _calculateApparentEquatorialCoordinates(Carbon $date): EquatorialCoordinates
    {
        $helio_coords       = $this->calculateHeliocentricCoordinates($date);
        $earth              = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);

        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $tau   = 0.0057755183 * $delta;

        $jd      = Time::getJd($date);
        $newDate = Time::fromJd($jd - $tau);

        $helio_coords = $this->calculateHeliocentricCoordinates($newDate);
        $x            = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);

        $tau   = 0.0057755183 * $delta;

        $lambda = rad2deg(atan2($y, $x));
        $beta   = rad2deg(atan2($z, sqrt($x ** 2 + $y ** 2)));

        $T     = ($jd - 2451545) / 36525;
        $e     = 0.016708634 - 0.000042037 * $T - 0.0000001267 * $T ** 2;
        $pi    = 102.93735 + 1.71946 * $T + 0.00046 * $T ** 2;
        $kappa = 20.49552;

        $sun  = new Sun();
        $Odot = $sun->calculateOdotBetaR($date)[0];

        $deltaLambda = ((-$kappa * cos(deg2rad($Odot - $lambda)) + $e * $kappa * cos(deg2rad($pi - $lambda))) / cos(deg2rad($beta))) / 3600.0;
        $deltaBeta   = (-$kappa * sin(deg2rad($beta)) * (sin(deg2rad($Odot - $lambda)) - $e * sin(deg2rad($pi - $lambda)))) / 3600.0;

        $lambda += $deltaLambda;
        $beta += $deltaBeta;

        $L_accent = $helio_coords[0] - 1.397 * ($T) - 0.00031 * ($T) ** 2;

        $deltaLambda = -0.09033 + 0.03916 * (cos(deg2rad($L_accent) + sin(deg2rad($L_accent)))) * tan(deg2rad($helio_coords[1]));
        $deltaBeta   = 0.03916 * (cos(deg2rad($L_accent)) - sin(deg2rad($L_accent)));

        $lambda += $deltaLambda / 3600.0;
        $beta += $deltaBeta / 3600.0;

        $nutation = Time::nutation($jd);

        $lambda += $nutation[0] / 3600.0;

        $ecl = new EclipticalCoordinates($lambda, $beta);

        return $ecl->convertToEquatorial($nutation[3]);
    }

    private function _calculateEquatorialCoordinates(Carbon $date, GeographicalCoordinates $geo_coords, float $height): EquatorialCoordinates
    {
        $helio_coords       = $this->calculateHeliocentricCoordinates($date);
        $earth              = new Earth();
        $helio_coords_earth = $earth->calculateHeliocentricCoordinates($date);

        $x = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $tau   = 0.0057755183 * $delta;

        $jd      = Time::getJd($date);
        $newDate = Time::fromJd($jd - $tau);

        $helio_coords = $this->calculateHeliocentricCoordinates($newDate);
        $x            = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * cos(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * cos(deg2rad($helio_coords_earth[0]));
        $y = $helio_coords[2] * cos(deg2rad($helio_coords[1])) * sin(deg2rad($helio_coords[0])) -
            $helio_coords_earth[2] * cos(deg2rad($helio_coords_earth[1])) * sin(deg2rad($helio_coords_earth[0]));
        $z = $helio_coords[2] * sin(deg2rad($helio_coords[1])) -
            $helio_coords_earth[2] * sin(deg2rad($helio_coords_earth[1]));
        $delta = sqrt($x ** 2 + $y ** 2 + $z ** 2);
        $tau   = 0.0057755183 * $delta;

        $lambda = rad2deg(atan2($y, $x));
        $beta   = rad2deg(atan2($z, sqrt($x ** 2 + $y ** 2)));

        $T     = ($jd - 2451545) / 36525;
        $e     = 0.016708634 - 0.000042037 * $T - 0.0000001267 * $T ** 2;
        $pi    = 102.93735 + 1.71946 * $T + 0.00046 * $T ** 2;
        $kappa = 20.49552;

        $sun  = new Sun();
        $Odot = $sun->calculateOdotBetaR($date)[0];

        $deltaLambda = ((-$kappa * cos(deg2rad($Odot - $lambda)) + $e * $kappa * cos(deg2rad($pi - $lambda))) / cos(deg2rad($beta))) / 3600.0;
        $deltaBeta   = (-$kappa * sin(deg2rad($beta)) * (sin(deg2rad($Odot - $lambda)) - $e * sin(deg2rad($pi - $lambda)))) / 3600.0;

        $lambda += $deltaLambda;
        $beta += $deltaBeta;

        $L_accent = $helio_coords[0] - 1.397 * ($T) - 0.00031 * ($T) ** 2;

        $deltaLambda = -0.09033 + 0.03916 * (cos(deg2rad($L_accent) + sin(deg2rad($L_accent)))) * tan(deg2rad($helio_coords[1]));
        $deltaBeta   = 0.03916 * (cos(deg2rad($L_accent)) - sin(deg2rad($L_accent)));

        $lambda += $deltaLambda / 3600.0;
        $beta += $deltaBeta / 3600.0;

        $nutation = Time::nutation($jd);

        $lambda += $nutation[0] / 3600.0;

        $ecl = new EclipticalCoordinates($lambda, $beta);

        $equa_coords = $ecl->convertToEquatorial($nutation[3]);

        // Calculate corrections for parallax
        $pi    = 8.794 / $delta;

        $siderial_time  = Time::apparentSiderialTime($date, new GeographicalCoordinates(0.0, 0.0));

        $hour_angle = (new \deepskylog\AstronomyLibrary\Coordinates\Coordinate($equa_coords->getHourAngle($siderial_time) + $geo_coords->getLongitude()->getCoordinate() * 15.0, 0, 360))->getCoordinate();

        $earthsGlobe = $geo_coords->earthsGlobe($height);

        $deltara     = rad2deg(atan(-$earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)) / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * sin(deg2rad($hour_angle)))));
        $dec         = rad2deg(atan((sin(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[0] * sin(deg2rad($pi / 3600.0))) * cos(deg2rad($deltara / 3600.0))
                        / (cos(deg2rad($equa_coords->getDeclination()->getCoordinate())) - $earthsGlobe[1] * sin(deg2rad($pi / 3600.0)) * cos(deg2rad($height)))));

        $equa_coords->setRA($equa_coords->getRA()->getCoordinate() + $deltara);
        $equa_coords->setDeclination($dec);

        return $equa_coords;
    }
}
