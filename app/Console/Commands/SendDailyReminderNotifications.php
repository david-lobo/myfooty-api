<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Library\MyFooty\PushNotifications\NotificationSender\DailyReminderSender;
use CustomLog as CLog;

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
        $cronPath = Config::get('custom-log.paths.cron');

        // create subdirectory using the logFilename
        $logPath = $cronPath . DIRECTORY_SEPARATOR . $this->logFilename;
        CLog::configureLogger($logPath, $this->logFilename, true);

        CLog::info('===================================');
        CLog::info('SendDailyReminderNotifications running');

        //DB::connection()->enableQueryLog();

        $notificationSender = new DailyReminderSender();
        $notificationSender->send();

        //$queries = DB::getQueryLog();
        //var_dump($queries);

        CLog::info('SendDailyReminderNotifications finished');
        CLog::info('===================================');
    }
}
