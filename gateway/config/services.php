<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use toubilib\gateway\api\actions\GenericGatewayAction;

return [
    // Client Guzzle pour l'API Toubilib COMPLÈTE (auth principalement)
    'client.toubilib' => function (ContainerInterface $c) {
        $baseUri = $_ENV['API_TOUBILIB_URL'] ?? 'http://api.toubilib:80';
        return new Client([
            'base_uri' => $baseUri,
            'timeout' => 30.0,
        ]);
    },
    
    // Client Guzzle pour le microservice PRATICIENS
    'client.praticiens' => function (ContainerInterface $c) {
        $baseUri = $_ENV['API_PRATICIENS_URL'] ?? 'http://app.praticiens:80';
        return new Client([
            'base_uri' => $baseUri,
            'timeout' => 30.0,
        ]);
    },
    
    // Client Guzzle pour le microservice RDV
    'client.rdv' => function (ContainerInterface $c) {
        $baseUri = $_ENV['API_RDV_URL'] ?? 'http://app.rdv:80';
        return new Client([
            'base_uri' => $baseUri,
            'timeout' => 30.0,
        ]);
    },
    
    // Action générique pour rediriger les requêtes
    // Elle choisira le bon client selon la route
    GenericGatewayAction::class => function (ContainerInterface $c) {
        return new GenericGatewayAction(
            $c->get('client.toubilib'),
            $c->get('client.praticiens'),
            $c->get('client.rdv')
        );
    },
];