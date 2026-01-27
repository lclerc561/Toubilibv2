<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\actions\GenericGatewayAction;

return function (App $app): App {

    // Route pour l'Authentification
    $app->map(['POST', 'GET'], '/auth/{routes:.+}', function($request, $response, $args) {
        // On récupère le container directement de l'instance $this (Slim le lie au callable)
        $client = $this->get('client.auth'); 
        $action = new GenericGatewayAction($client);
        return $action($request, $response, $args);
    });

    // Route pour Toubilib
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($request, $response, $args) {
        $client = $this->get('client.toubilib');
        $action = new GenericGatewayAction($client);
        return $action($request, $response, $args);
    });

    return $app;
};