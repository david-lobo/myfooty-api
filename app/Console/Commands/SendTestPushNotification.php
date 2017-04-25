<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

class SendTestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:send-test-push {token} {--server=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test push notification using configured settings';

    /**
     * Create a new command instance.
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
        $useProduction = false;
        $configNameDev = 'myfooty_development';
        $configNameProd = 'myfooty_production';

        $this->info('SendTestPushNotification running');
        $token = $this->argument('token');
        $server = $this->option('server');
        $this->info("Sending to '{$token}'");

        if (empty($server) || !in_array($server, ['dev', 'prod'])) {
            $this->error("Invalid option for server");
            return;
        }

        if ($server == 'prod') {
            $useProduction = true;
        }

        $configName = $configNameDev;
        $message = 'This is a test push message sent ';
        $log = 'using development APNS server';
        if ($useProduction) {
            $configName = $configNameProd;
            $log = 'using production APNS server';
        }

        $this->info($log);
        $message = "{$message} {$log}";

        PushNotification::app($configName)
                ->to($token)
                ->send($message);
    }
}
