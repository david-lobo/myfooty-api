<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'tasks' => [
        snake_case(App\Console\Commands\SendDailyReminderNotifications::class) => [
            'send_time_hour' => 21
        ],

    ],

];
