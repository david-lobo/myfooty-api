<?php

return [
        'myfooty_development' => [
            'environment' => 'development',
            'certificate' => storage_path() . '/app/keys/notifications/apns/dev/ck.pem',
            'passPhrase'  => '',
            'service'     => 'apns'
        ],
        'myfooty_production' => [
            'environment' => 'production',
            'certificate' => storage_path() . '/app/keys/notifications/apns/prod/ck_prod.pem',
            'passPhrase'  => '',
            'service'     => 'apns'
        ],
];
