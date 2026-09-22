<?php

namespace deepskylog\AstronomyLibrary\Imports;

use deepskylog\AstronomyLibrary\Models\DeltaT;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;

class DeltaTImport implements ToModel
{
    /**
     * @param  array  $row  The row of the csv file
     * @return Model|array<int, Model>|null
     */
    public function model(array $row): Model|array|null
    {
        return new DeltaT(
            [
                'year' => $row[0],
                'deltat' => $row[1],
            ]
        );
    }
}
