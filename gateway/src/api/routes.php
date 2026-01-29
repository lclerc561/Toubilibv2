<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\actions\GenericGatewayAction;
use toubilib\gateway\api\middlewares\AuthNMiddleware;

return function(App $app): App {
    $app->post('/auth/login', GenericGatewayAction::class);
    $app->post('/auth/register', GenericGatewayAction::class);

    $app->group('', function($group) {
        $group->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', GenericGatewayAction::class);
    })->add(AuthNMiddleware::class);

    return $app;
};