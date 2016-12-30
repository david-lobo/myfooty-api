<?php

$apiLocalPath = "http://api.myfooty.local/api/v1/";
$apiLivePath = "http://api.myfooty.co.uk/api/v1/";

return [
    "local" => [
        "params" => [
            "ClubsUrl" => "{$apiLocalPath}clubs",
            "FixturesUrl" => "{$apiLocalPath}fixtures",
            "APIAvailable" => "true"
        ]
    ],
    "live" => [
        "params" => [
            "ClubsUrl" => "{$apiLivePath}clubs",
            "FixturesUrl" => "{$apiLivePath}fixtures",
            "APIAvailable" => "false"
        ]
    ],
];
