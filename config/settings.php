<?php


return [

    'jwt' => array(
        'lifetime' => env('JWT_LIFETIME', 3600),
        'algorithm' => 'SHA256',
        'headers' => array(
            "alg" => 'HS256',
            "type" => 'JWT'
        ),
        'secret' => env('JWT_SECRET', 'secret')
    )

];
