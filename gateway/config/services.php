<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

return [
    // Client pour le microservice authentification
    'client.auth' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $_ENV['API_AUTH_URL'] ?? 'http://api.auth:80',
            'timeout'  => 30.0,
        ]);
    },

    // Client pour le service métier (toubilib)
    'client.toubilib' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $_ENV['API_TOUBILIB_URL'] ?? 'http://api.toubilib:80',
            'timeout'  => 30.0,
        ]);
    },

    // Client par défaut pour éviter l'erreur de conteneur
    Client::class => function (ContainerInterface $c) {
        return $c->get('client.toubilib');
    },
];