<?php

namespace deepskylog\AstronomyLibrary\Imports;

use deepskylog\AstronomyLibrary\Models\ConstellationBoundaries;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ConstellationBoundariesImport implements ToModel, WithCustomCsvSettings
{
    /**
     * @param  array  $row  The row of the csv file
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new ConstellationBoundaries(
            [
                'con0' => $row[0],
                'con0pos' => $row[1],
                'con1' => $row[2],
                'con1pos' => $row[3],
                'ra0' => $row[4],
                'decl0' => $row[5],
                'ra1' => $row[6],
                'decl1' => $row[7],
            ]
        );
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
        ];
    }
}
