<?php

namespace deepskylog\AstronomyLibrary\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define laravel-astronomy-library's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule The schedule
     */
    protected function schedule(Schedule $schedule)
    {
        parent::schedule($schedule);

        $schedule->command(
            'deltat:update'
        )->quarterly();
    }
}
