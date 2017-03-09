<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        'App\Console\Commands\ImportFixturesFromFile',
        'App\Console\Commands\UpdateFixturesModel',
        'App\Console\Commands\ExportSettings',
        'App\Console\Commands\SetupTeamConfig',
        'App\Console\Commands\SendDailyReminderNotifications',
        'App\Console\Commands\SendKickoffReminderNotifications',
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
        /*$schedule->exec('cd ~/Code/myfooty-api && ls')
            ->everyMinute()
            ->sendOutputTo('/home/vagrant/Code/myfooty-api/listing.txt');*/

        /*$schedule->call(function () {
            Log::info("scheduler is running");
        })->everyFiveMinutes()->between('11:00', '22:00');;*/


    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
