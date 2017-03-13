<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Library\MyFooty\PushNotifications\NotificationSender\DailyReminderSender;
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use CustomLog as CLog;
use Carbon\Carbon;

class SendDailyReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:send-daily-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Daily APN notifications';

    /**
     * The name of custom log file.
     *
     * @var string
     */
    protected $logFilename = 'daily';

    /**
     * Create a new SendDailyReminderNotifications instance.
     *
     * @return void
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


        $className = self::class;
        $taskName = snake_case($className);
        $cronPath = Config::get('custom-log.paths.cron');
        $sendTimeHour = Config::get("schedule.tasks.$taskName.send_time_hour");

        // create subdirectory using the logFilename
        $logPath = $cronPath . DIRECTORY_SEPARATOR . $this->logFilename;
        CLog::configureLogger($logPath, $this->logFilename, true);

        if (!is_int($sendTimeHour)) {
            throw new \InvalidArgumentException("Config variable 'send_time_hour' must be an int", 1);
        }

        if (!($sendTimeHour >= 0 && $sendTimeHour < 24)) {
            throw new \InvalidArgumentException("Config variable 'send_time_hour' must be an int between 0-23", 2);
        }

        var_dump($className);
        var_dump($sendTimeHour);

        $dateNow = Carbon::now();

        /*if ($sendTimeHour != $dateNow->hour) {
            CLog::info('SendDailyReminderNotifications called at wrong time', [
                    'sendTimeHour' => $sendTimeHour,
                    'dateNow' => $dateNow
                ]);
            return;
        }*/

        CLog::info('===================================');
        CLog::info('SendDailyReminderNotifications running');

        //DB::connection()->enableQueryLog();

        //$dateNow->day = 11;
        //$dateNow->hour = 10;
        //$dateNow->minute = 0;

        $notificationSender = new DailyReminderSender(MessageSender::SEND_MODE_APNS);
        $notificationSender->setDateNow($dateNow);
        $notificationSender->send();

        //$queries = DB::getQueryLog();
        //var_dump($queries);

        CLog::info('SendDailyReminderNotifications finished');
        CLog::info('===================================');
    }
}
