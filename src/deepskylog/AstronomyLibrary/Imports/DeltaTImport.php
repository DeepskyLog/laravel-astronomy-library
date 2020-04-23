<?php

namespace deepskylog\AstronomyLibrary\Imports;

use deepskylog\AstronomyLibrary\DeltaT;
use Maatwebsite\Excel\Concerns\ToModel;

class DeltaTImport implements ToModel
{
    // TODO: remove link to test package.

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new DeltaT(
            [
                'year' => $row[0],
                'deltat' => $row[1]
            ]
        );
    }
}
