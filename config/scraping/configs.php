<?php

$storagePath = storage_path();
$dataDir = "scraping" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR;
$mockDataDir = "scraping" . DIRECTORY_SEPARATOR . "mock-data" . DIRECTORY_SEPARATOR;
$localDir = $dataDir . "local" . DIRECTORY_SEPARATOR;
$liveDir = $dataDir . "live" . DIRECTORY_SEPARATOR;

$fixturesPath = 'pending' . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR;
$competitions = [
    [
        "id" => 1,
        "compSeasons" => 54
    ],
    [
        "id" => 2,
        "compSeasons" => 66
    ],
    [
        "id" => 3,
        "compSeasons" => 70
    ],
    [
        "id" => 4,
        "compSeasons" => 32
    ],
    [
        "id" => 5,
        "compSeasons" => 56
    ]
];

return [
	"local" => [
		"paths" =>  [
			"scraping" => $localDir,
			"fixtures" => $localDir . $fixturesPath,
			"mock" => $mockDataDir
		],
		"urls" => [
	    	"home" => "http://api.myfooty.local/api/mock/broadcasting-schedule/fixtures",
	      	"fixtures" => "http://api.myfooty.local/api/mock/fixtures",
	      	"schedule" => "http://api.myfooty.local/api/mock/broadcasting-schedule/fixtures"
	     ],
		"competitions" => $competitions
	],
	"live" => [
		"paths" =>  [
			"scraping" => $liveDir,
			"fixtures" => $liveDir . $fixturesPath,
			"mock" => $mockDataDir
		],
		"urls" => [
	      	"home" => "https://www.premierleague.com/fixtures",
	      	"fixtures" => "https://footballapi.pulselive.com/football/fixtures",
	      	"schedule" => "https://footballapi.pulselive.com/football/broadcasting-schedule/fixtures"
	    ],
		"competitions" => $competitions
	]
];
