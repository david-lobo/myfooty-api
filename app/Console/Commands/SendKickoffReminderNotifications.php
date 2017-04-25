<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Config;
use Library\MyFooty\PushNotifications\NotificationSender\KickoffReminderSender;
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use CustomLog as CLog;


class SendKickoffReminderNotifications extends SendMatchReminderNotifications
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:send-kickoff-reminders {date?} {--server=}';

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
    //protected $logFilename = 'kickoff';

    /**
     * Create a new SendKickoffReminderNotifications instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->logFilename = 'kickoff';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        // Set the date here only for testing
        //$dateNow->day = 11;
        //$dateNow->hour = 19;
        //$dateNow->minute = 15;

        $notificationSender = new KickoffReminderSender(MessageSender::SEND_MODE_APNS, $this->pushApp);
        $notificationSender->setDateNow($this->dateNow);
        $notificationSender->send();

        //$queries = DB::getQueryLog();
        //var_dump($queries);

        CLog::info("{$this->getClassName()} finished");
        CLog::info('===================================');
    }
}
