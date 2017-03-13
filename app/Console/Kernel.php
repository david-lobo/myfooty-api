<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Console\Commands\SendDailyReminderNotifications;
use Carbon\Carbon;

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
        /*$schedule->call(function () {
            Log::info("scheduler is running");
        })->everyMinute();*/

        $this->scheduleKickoffReminders($schedule);
        $this->scheduleDailyReminders($schedule);
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

    /**
     * Schedule the kickoff reminders
     *
     * @return void
     */
    protected function scheduleKickoffReminders(Schedule $schedule)
    {
        $schedule->command('fixtures:send-kickoff-reminders')->everyMinute()->between('11:00', '22:00');
    }

    /**
     * Schedule the daily reminders
     *
     * @return void
     */
    protected function scheduleDailyReminders(Schedule $schedule)
    {
        $className = SendDailyReminderNotifications::class;
        $taskName = snake_case($className);
        $sendTimeHour = Config::get("schedule.tasks.$taskName.send_time_hour");

        if (!is_int($sendTimeHour)) {
            throw new \InvalidArgumentException("Config variable 'send_time_hour' must be an int", 1);
        }

        if (!($sendTimeHour >= 0 && $sendTimeHour < 24)) {
            throw new \InvalidArgumentException("Config variable 'send_time_hour' must be an int between 0-23", 2);
        }

        $dateNow = Carbon::now();
        $dateNow->hour = $sendTimeHour;
        $dateNow->minute = 0;
        $dateNow->second = 0;

        $sendTime = $dateNow->format('H:i');

        /*Log::info("Scheduling Daily Reminders for $sendTime", [
            'sendTimeHour' => $sendTimeHour
        ]);*/

        $schedule->command('fixtures:send-daily-reminders')->dailyAt($sendTime);
    }
}
