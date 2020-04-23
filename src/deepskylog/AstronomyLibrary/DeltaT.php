<?php

namespace deepskylog\AstronomyLibrary;

use Illuminate\Database\Eloquent\Model;

class DeltaT extends Model
{
    public $timestamps = false;

    protected $table = 'delta_t';

    protected $fillable = [
        'year', 'deltat'
    ];
}
