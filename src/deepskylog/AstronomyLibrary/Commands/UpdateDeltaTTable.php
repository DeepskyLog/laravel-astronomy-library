<?php

namespace deepskylog\AstronomyLibrary\Commands;

use deepskylog\AstronomyLibrary\DeltaT;
use deepskylog\AstronomyLibrary\Imports\DeltaTImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class UpdateDeltaTTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deltat:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the delta t value in the database with the latest value from the internet.';

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
        // Download new file from github
        $contents = file_get_contents(
            'https://raw.githubusercontent.com/DeepskyLog/laravel-astronomy-library/master/data/deltat.csv'
        );

        file_put_contents('/tmp/deltat.csv', $contents);

        // Remove the old entries
        DeltaT::truncate();

        // Import the file in the database.
        Excel::import(new DeltaTImport(), '/tmp/deltat.csv');
    }
}
