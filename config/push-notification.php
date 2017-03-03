<?php

return array(

    'appNameIOS'     => array(
        'environment' =>'development',
        'certificate' => storage_path() . '/app/keys/notifications/apns/dev/ck.pem',
        'passPhrase'  => '',
        'service'     =>'apns'
    ),
    'appNameAndroid' => array(
        'environment' =>'production',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);
