<?php

namespace App\Library\MyFooty\CustomLog\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CustomLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('CustomLog', function () {
            return new \App\Library\MyFooty\CustomLog\CustomLog;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        return [Logger::class];
    }
}
