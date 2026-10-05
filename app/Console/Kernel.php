<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\Activitylog\Models\Activity;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
         Commands\NotificationCommand::class,
         \Modules\BackOffice\Console\TerminationDuesBackfill::class,
         \Modules\BackOffice\Console\TerminationDuesRefresh::class,
         \Modules\BackOffice\Console\RentOutstandingCorrectShortContracts::class,
         \Modules\BackOffice\Console\RentOutstandingApplyDayCount::class,
         \Modules\BackOffice\Console\BulkMessagesSend::class,
         \Modules\BackOffice\Console\RefreshReceivableFollowup::class,
         \Modules\BackOffice\Console\PdcConvertVat::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        

       $schedule->command('taskOverDuenotify:command')->everyMinute();

       $schedule->command('termination-dues:refresh --open-only')->dailyAt('01:30');

       $schedule->command('bulk-messages:send')->everyMinute()->withoutOverlapping(10);

       $schedule->command('receivables:refresh-followup')->everyFifteenMinutes()->withoutOverlapping(30);

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
