<?php

namespace deepskylog\AstronomyLibrary\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define laravel-astronomy-library's command schedule.
     *
     * @param  Schedule  $schedule  The schedule
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

    /**
     * Public wrapper to allow external callers (like the package service provider)
     * to invoke the schedule method. The actual schedule method is protected
     * (as defined by Laravel), so expose a thin public shim that forwards to it.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    public function callSchedule(Schedule $schedule): void
    {
        $this->schedule($schedule);
    }
}
