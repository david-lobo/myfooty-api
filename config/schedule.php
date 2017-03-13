<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Schedule Enabled
    |--------------------------------------------------------------------------
    |
    | Enable the execution of scheduled tasks
    |
    */

   'enabled' => env('SCHEDULE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Tasks
    |--------------------------------------------------------------------------
    |
    | Config variables for the different scheduled tasks
    |
    */

    'tasks' => [
        snake_case(App\Console\Commands\SendDailyReminderNotifications::class) => [
            'send_time_hour' => 10
        ],

    ],

];
