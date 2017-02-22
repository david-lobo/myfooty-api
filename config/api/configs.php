<?php

$baseUrlLocal = "http://api.myfooty.local";
$baseUrlLive = "http://api.myfooty.co.uk";

$apiDataPath = "api/v1/data/export";

return [
    "local" => [
        "params" => [
            //"ClubsUrl" => "{$apiLocalPath}clubs",
            "base_url" => $baseUrlLocal,
            "api_data_path" => $apiDataPath,
            "api_available" => false,
            "data_version" => "v1"
        ]
    ],
    "live" => [
        "params" => [
            //"ClubsUrl" => "{$apiLivePath}clubs",
            "base_url" => $baseUrlLocal,
            "api_data_path" => $apiDataPath,
            "api_available" => false,
            "data_version" => "v1"
        ]
    ],
];
