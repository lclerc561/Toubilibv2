<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use toubilib\gateway\api\actions\ListPraticiensAction;

return [
    // Client Guzzle pour interroger l'API Toubilib
    Client::class => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => 'http://api.toubilib:80',
            'timeout' => 30.0,
        ]);
    },
    
    // Action pour lister les praticiens
    ListPraticiensAction::class => function (ContainerInterface $c) {
        return new ListPraticiensAction($c->get(Client::class));
    },
];
