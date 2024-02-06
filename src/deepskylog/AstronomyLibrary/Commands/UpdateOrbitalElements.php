<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\Models\AsteroidsOrbitalElements;
use deepskylog\AstronomyLibrary\Models\CometsOrbitalElements;
use Illuminate\Console\Command;

class UpdateOrbitalElements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astronomy:updateOrbitalElements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the orbital elements of comets and asteroids.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Download new orbital elements for comets from the JPL website
        $contents = file_get_contents(
            'https://ssd.jpl.nasa.gov/dat/ELEMENTS.COMET'
        );

        // Remove the old entries
        CometsOrbitalElements::truncate();

        $cnt = 0;
        // Loop over the orbital elements line by line
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $contents) as $line) {
            if ($cnt > 1) {
                // The first 43 characters are the name
                $name = trim(substr($line, 0, 43));
                // Character 44 - 51 is the epoch
                $epoch = intval(substr($line, 44, 8)) + 2400000.5;
                // Character 52 - 63 is q: perihelion distance in AU
                $q = floatval(substr($line, 52, 12));
                // Character 64 - 75 is e, the eccentricity of the orbit
                $e = floatval(substr($line, 64, 11));
                // Character 75 - 85 is i, the inclination of the orbit
                $i = floatval(substr($line, 75, 10));
                // w: The argument of perihelion
                $w = floatval(substr($line, 85, 10));
                // node: Longitude of the ascending node
                $node = floatval(substr($line, 95, 10));
                // Tp: Time of perihelion passage
                $Tp = floatval(substr($line, 105, 15));
                // Ref: The orbital solution reference
                $ref = trim(substr($line, 120));

                if ($name != '') {
                    CometsOrbitalElements::create(
                        [
                            'name' => $name,
                            'epoch' => $epoch,
                            'q' => $q,
                            'e' => $e,
                            'i' => $i,
                            'w' => $w,
                            'node' => $node,
                            'Tp' => $Tp,
                            'ref' => $ref,
                        ]
                    );
                }
            }
            $cnt++;
        }

        // Download new orbital elements for asteroids from the JPL website
        $contents = file_get_contents(
            'https://ssd.jpl.nasa.gov/dat/ELEMENTS.NUMBR'
        );

        // Remove the old entries
        AsteroidsOrbitalElements::truncate();

        $cnt = 0;
        // Loop over the orbital elements line by line
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $contents) as $line) {
            if ($cnt > 1) {
                // Character 0 - 6 is the number
                $number = intval(substr($line, 0, 6));
                // Characters 7 - 25 is the name
                $name = trim(substr($line, 7, 18));
                // Character 25 - 31 is the epoch
                $epoch = intval(substr($line, 25, 6)) + 2400000.5;
                // Character 31 - 42 is a: semi-major axis in AU
                $a = floatval(substr($line, 31, 11));
                // Character 42 - 53 is e, the eccentricity of the orbit
                $e = floatval(substr($line, 42, 11));
                // Character 53 - 63 is i, the inclination of the orbit
                $i = floatval(substr($line, 53, 10));
                // w: The argument of perihelion
                $w = floatval(substr($line, 63, 10));
                // node: Longitude of the ascending node
                $node = floatval(substr($line, 73, 10));
                // M: Mean anomaly
                $M = floatval(substr($line, 83, 12));
                // H: Absolute magnitude
                $H = floatval(substr($line, 95, 6));
                // G: Magnitude slope parameter
                $G = floatval(substr($line, 101, 6));
                // Ref: The orbital solution reference
                $ref = trim(substr($line, 107));

                if ($name != '') {
                    AsteroidsOrbitalElements::create(
                        [
                            'number' => $number,
                            'name' => $name,
                            'epoch' => $epoch,
                            'a' => $a,
                            'e' => $e,
                            'i' => $i,
                            'w' => $w,
                            'node' => $node,
                            'M' => $M,
                            'H' => $H,
                            'G' => $G,
                            'Tp' => $Tp,
                            'ref' => $ref,
                        ]
                    );
                }
            }
            $cnt++;
        }
    }
}
