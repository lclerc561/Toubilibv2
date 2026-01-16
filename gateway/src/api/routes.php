<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\actions\GenericGatewayAction;

return function(App $app): App {
    // Route générique catch-all pour rediriger toutes les requêtes vers l'API Toubilib
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', GenericGatewayAction::class);
    
    return $app;
};
