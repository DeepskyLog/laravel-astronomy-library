<?php

namespace deepskylog\AstronomyLibrary\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use deepskylog\AstronomyLibrary\Models\DeltaT;

class DeltaTImport implements ToModel
{
    /**
     * @param array $row The row of the csv file
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new DeltaT(
            [
                'year' => $row[0],
                'deltat' => $row[1],
            ]
        );
    }
}
