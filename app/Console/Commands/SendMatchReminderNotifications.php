<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Library\MyFooty\PushNotifications\NotificationSender\KickoffReminderSender;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Library\MyFooty\PushNotifications\MessageSender\MessageSender;
use CustomLog as CLog;
use Carbon\Carbon;

class SendMatchReminderNotifications extends Command
{
    /**
     * The name of custom log file.
     *
     * @var string
     */
    protected $logFilename;

    /**
     * The name and signature of the console command.
     *
     * @var \Davibennun\LaravelPushNotification\App
     */
    protected $pushApp;

    /**
     * Date to use for queries
     *
     * @var \Carbon\Carbon
     */
    protected $dateNow;

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
        // Path for custom log files
        $cronPath = Config::get('custom-log.paths.cron');

        // create subdirectory using the logFilename
        $logPath = $cronPath . DIRECTORY_SEPARATOR . $this->logFilename;

        // configure custom logger for this class
        CLog::configureLogger($logPath, $this->logFilename, true);

        // env variable for APNS server to use
        $apnsServerEnv = env('APNS_SERVER', null);

        // allowed APNS servers
        $apnsServers = ['dev', 'prod'];
        $apnsServerToUse = 'dev';

        CLog::info('===================================');
        CLog::info("{$this->getClassName()} running");
        CLog::info("{$logPath}/{$this->logFilename}");

        //DB::connection()->enableQueryLog();

        $dateArg = $this->argument('date');
        $serverOpt = $this->option('server');
        $this->dateNow = Carbon::now();

        if (!empty($dateArg)) {
            try {
                $dateToUse = Carbon::createFromFormat('Y-m-d H:i:s', $dateArg);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid date argument {$dateArg}", 3);
            }
            if (!empty($dateToUse)) {
                CLog::info('Using date argument', [$dateArg]);
                $this->dateNow = $dateToUse;
            }
        }

        if (!empty($serverOpt)) {
            if (!in_array($serverOpt, $apnsServers)) {
                $this->error("Invalid option for server");
                throw new \InvalidArgumentException("Invalid option for server {$serverOpt}", 4);
            }

            $apnsServerToUse = $serverOpt;
        } else {
            if (!in_array($apnsServerEnv, $apnsServers)) {
                $this->error("Invalid env variable for APNS_SERVER");
                throw new \InvalidArgumentException("Invalid option for server {$apnsServerEnv}", 5);
            }

             $apnsServerToUse = $apnsServerEnv;
        }

        if ($apnsServerToUse == 'prod') {
            CLog::info('Production APNS server specified');

            // TODO - Change this to point to PROD config
            $this->pushApp = PushNotification::app('myfooty_development');
        } else {
            CLog::info('Development APNS server specified');
            $this->pushApp = PushNotification::app('myfooty_development');
        }
    }

    /**
     * Get nicely formatted class name
     *
     * @return string
     */
    public function getClassName()
    {
        $path = explode('\\', static::class);
        return array_pop($path);
    }
}
