<?php

namespace deepskylog\AstronomyLibrary\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define laravel-astronomy-library's command schedule.
     *
     * @param Schedule $schedule  The schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        parent::schedule($schedule);

        $schedule->command(
            'astronomy:updateDeltat'
        )->quarterly();

        $schedule->command(
            'astronomy:updateOrbitalElements'
        )->weeklyOn(1, '4:30');
    }
}
