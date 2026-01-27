<?php

return [
    'displayErrorDetails' => true,
     
    "api-auth" => [
        // Correction : api.auth au lieu de app.auth
        'base_uri' => 'http://api.auth:80',   
        'timeout'  => 15.0
    ],
     
    "api-toubilib" => [
        // Correction : api.toubilib au lieu de app.praticiens/rdv
        'base_uri' => 'http://api.toubilib:80',  
        'timeout'  => 15.0
    ],
];