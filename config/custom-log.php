<?php

$basePath = storage_path() . DIRECTORY_SEPARATOR . 'logs';
$cronPath = $basePath . DIRECTORY_SEPARATOR . 'cron';

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        'base' => $basePath,
        'cron' => $cronPath
    ],
];
