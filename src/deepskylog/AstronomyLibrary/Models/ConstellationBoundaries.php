<?php

namespace deepskylog\AstronomyLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class ConstellationBoundaries extends Model
{
    public $timestamps = false;

    protected $table = 'constellation_boundaries';

    protected $fillable = [
        'con0', 'con0pos', 'con1', 'con1pos', 'ra0', 'decl0', 'ra1', 'decl1',
    ];
}
