<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use toubilib\gateway\api\actions\ListPraticiensAction;
use toubilib\gateway\api\actions\DetailPraticien;

return [
    // Client Guzzle pour interroger l'API Toubilib
    Client::class => function (ContainerInterface $c) {
        $baseUri = $_ENV['API_TOUBILIB_URL'] ?? 'http://api.toubilib:80';
        return new Client([
            'base_uri' => $baseUri,
            'timeout' => 30.0,
        ]);
    },
    
    // Action pour lister les praticiens
    ListPraticiensAction::class => function (ContainerInterface $c) {
        return new ListPraticiensAction($c->get(Client::class));
    },
    
    // Action pour obtenir les détails d'un praticien
    DetailPraticien::class => function (ContainerInterface $c) {
        return new DetailPraticien($c->get(Client::class));
    },
];
