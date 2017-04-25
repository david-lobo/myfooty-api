<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Config;
use Library\MyFooty\PushNotifications\NotificationSender\DailyReminderSender;
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use CustomLog as CLog;

class SendDailyReminderNotifications extends SendMatchReminderNotifications
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:send-daily-reminders {date?} {--server=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Daily APN notifications';

    /**
     * Create a new SendDailyReminderNotifications instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->logFilename = 'daily';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $className = self::class;
        $taskName = snake_case($className);
        $sendTimeHour = Config::get("schedule.tasks.$taskName.send_time_hour");

        $notificationSender = new DailyReminderSender(MessageSender::SEND_MODE_APNS, $this->pushApp);
        $notificationSender->setDateNow($this->dateNow);
        $notificationSender->send();

        //$queries = DB::getQueryLog();
        //var_dump($queries);

        CLog::info("{$this->getClassName()} finished");
        CLog::info('===================================');
    }
}
