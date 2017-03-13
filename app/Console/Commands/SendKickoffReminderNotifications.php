<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Library\MyFooty\PushNotifications\NotificationSender\KickoffReminderSender;
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use CustomLog as CLog;
use Carbon\Carbon;

class SendKickoffReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:send-kickoff-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send match kickoff APN notifications';

    /**
     * The name of custom log file.
     *
     * @var string
     */
    protected $logFilename = 'kickoff';

    /**
     * Create a new SendKickoffReminderNotifications instance.
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
        CLog::info('SendKickoffReminderNotifications running');

        //DB::connection()->enableQueryLog();

        $dateNow = Carbon::now();

        // Set the date here only for testing
        //$dateNow->day = 11;
        //$dateNow->hour = 19;
        //$dateNow->minute = 15;

        $notificationSender = new KickoffReminderSender(MessageSender::SEND_MODE_APNS);
        $notificationSender->setDateNow($dateNow);
        $notificationSender->send();

        //$queries = DB::getQueryLog();
        //var_dump($queries);

        CLog::info('SendKickoffReminderNotifications finished');
        //CLog::info('===================================');
    }
}
