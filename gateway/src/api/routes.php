<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\actions\GenericGatewayAction;
use toubilib\gateway\api\middlewares\AuthNMiddleware;

return function(App $app): App {
    // Routes publiques (sans authentification)
    $app->post('/auth/login', GenericGatewayAction::class);
    $app->post('/auth/register', GenericGatewayAction::class);
    $app->post('/auth/refresh', GenericGatewayAction::class);

    // Routes publiques selon le TP 1 (opérations 1, 2, 3, 9)
    $app->get('/praticiens', GenericGatewayAction::class); // Lister praticiens
    $app->get('/praticiens/search', GenericGatewayAction::class); // Rechercher praticiens
    $app->get('/praticiens/{id}', GenericGatewayAction::class); // Détail praticien
    $app->get('/praticiens/{id}/rdvs/occupes', GenericGatewayAction::class); // Créneaux occupés

    // Routes protégées nécessitant une authentification
    $app->group('', function($group) {
        $group->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', GenericGatewayAction::class);
    })->add(AuthNMiddleware::class);

    return $app;
};