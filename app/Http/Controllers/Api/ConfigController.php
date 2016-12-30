<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class ConfigController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @return Response
     */
    public function __invoke($env)
    {
        $env = $env == "local" ? "local" : "live";
        $params = config("api.configs.{$env}.params");

        return response()
            ->view('config', ['params' => $params], 200)
            ->header('Content-Type', 'text/xml');
    }
}
