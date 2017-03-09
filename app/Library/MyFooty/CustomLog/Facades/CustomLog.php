<?php

namespace App\Library\MyFooty\CustomLog\Facades;
use Illuminate\Support\Facades\Facade;

class CustomLog extends Facade{
    protected static function getFacadeAccessor() { return 'CustomLog'; }
}
