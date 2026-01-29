<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use toubilib\gateway\api\actions\GenericGatewayAction;
use toubilib\gateway\api\middlewares\AuthNMiddleware;

return [
    'client.auth' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $_ENV['API_AUTH_URL'] ?? 'http://app.auth:80',
            'timeout'  => 30.0,
        ]);
    },

    'client.toubilib' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $_ENV['API_TOUBILIB_URL'] ?? 'http://api.toubilib:80',
            'timeout' => 30.0,
        ]);
    },
    
    'client.praticiens' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $_ENV['API_PRATICIENS_URL'] ?? 'http://app.praticiens:80',
            'timeout' => 30.0,
        ]);
    },
    
    'client.rdv' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => $_ENV['API_RDV_URL'] ?? 'http://app.rdv:80',
            'timeout' => 30.0,
        ]);
    },

    GenericGatewayAction::class => function (ContainerInterface $c) {
        return new GenericGatewayAction(
            $c->get('client.toubilib'),
            $c->get('client.praticiens'),
            $c->get('client.rdv'),
            $c->get('client.auth')
        );
    },

    AuthNMiddleware::class => function($c) {
        return new AuthNMiddleware($c->get('client.auth'));
    },

    Client::class => fn(ContainerInterface $c) => $c->get('client.toubilib'),
];