<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

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

        // Update the version to the latest from db
        $results = DB::select('select * from api_versions limit 1');
        if (!empty($results)) {
            $id = $results[0]->id;
            $params["version"] = "v{$id}";
        }

        return response()->json([
            'status' => 'success',
            'data' =>   [
                'config' => $params
                ]
            ]);

        /*return response()
            ->view('config', ['params' => $params], 200)
            ->header('Content-Type', 'text/xml');*/
    }
}
