<?php

namespace App\Imports;

use App\DeltaT;
use Maatwebsite\Excel\Concerns\ToModel;

class DeltaTImport implements ToModel
{
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
