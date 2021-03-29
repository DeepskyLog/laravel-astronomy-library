<?php

namespace deepskylog\AstronomyLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class AsteroidsOrbitalElements extends Model
{
    public $timestamps = false;

    protected $table = 'asteroids_orbital_elements';

    protected $fillable = [
        'number', 'name', 'epoch', 'a', 'e', 'i', 'w', 'node', 'M', 'H', 'G', 'ref',
    ];
}
