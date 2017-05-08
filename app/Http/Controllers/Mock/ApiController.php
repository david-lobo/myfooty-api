<?php

namespace App\Http\Controllers\Mock;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fixtures(Request $request)
    {
    	if ($request->has("page") && $request->has("comps")) {
	    	$settings = config()->get('scraping.settings');
	    	$mockDir = $settings["paths"]["mock"];

	    	$competitionId = $request->input("comps");
	    	$page = $request->input("page");

	    	$filename = "page${page}.json";

	    	$filePath = $mockDir . 'pages' .DIRECTORY_SEPARATOR . $competitionId . DIRECTORY_SEPARATOR . $filename;

	    	//var_dump($settings);
	    	//var_dump($filePath);

	    	if (Storage::exists($filePath)) {
	    		$json = Storage::get($filePath);

	    		return response($json);
	    	} else {

	    		$response = [
	    			"pageInfo" => [
	    				"page" => 0,
	    				"numPages" => 0,
	    				"pageSize" => 40,
	    				"numEntries" => 0
	    			],
	    			"content" => []
	    		];

	    		return response()->json($response);
	    	}
    	}

    	abort(400, 'Missing parameters in request');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function broadcastingSchedule(Request $request)
    {

    	if ($request->has("fixtureIds")) {
	    	$settings = config()->get('scraping.settings');
	    	$mockDir = $settings["paths"]["mock"];

	    	$fixtureIdsString = $request->input("fixtureIds");
	    	$fixtureIds = explode(',', $fixtureIdsString);
			$fixtureId = $fixtureIds[0];

	    	$page = $request->input("page");

	    	$filename = "page${fixtureId}.json";

	    	$filePath = $mockDir . 'broadcasting-schedule' . DIRECTORY_SEPARATOR;

	    	$filePath .= 'pages' .DIRECTORY_SEPARATOR . $filename;

	    	if (Storage::exists($filePath)) {
	    		$json = Storage::get($filePath);

	    		return response($json);
	    	} else {

	    		$response = [
	    			"countryCode" => "GB",
	    			"broadcasters" => [
	    				[
	    					"name" => "UK - Sky Sports",
	    					"abbreviation" => "SKY",
	    					"url" => "http://www.skysports.com/"
	    				],
	    				[
	    					"name" => "UK - BT Sport",
	    					"abbreviation" => "BT",
	    					"url" => "http://sport.bt.com/"
	    				]
	    			],
	    			"pageInfo" => [
	    				"page" => 0,
	    				"numPages" => 0,
	    				"pageSize" => 100,
	    				"numEntries" => 0
	    			],
	    			"content" => []
	    		];

	    		return response()->json($response);
	    	}
    	}

    	abort(400, 'Missing parameters in request');
    }
}
