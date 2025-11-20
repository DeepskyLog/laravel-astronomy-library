<?php

namespace deepskylog\AstronomyLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class CometsOrbitalElements extends Model
{
    public $timestamps = false;

    protected $table = 'comets_orbital_elements';

    protected $fillable = [
        'name',
        'epoch',
        'q',
        'e',
        'w',
        'i',
        'node',
        'Tp',
        'ref',
        // Photometry
        'H',
        'n',
        'phase_coeff',
        'n_pre',
        'n_post',
    ];
}
