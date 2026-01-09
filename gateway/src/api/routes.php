<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\actions\ListPraticiensAction;

return function(App $app): App {
    // Route pour accéder à la liste des praticiens
    $app->get('/praticiens', ListPraticiensAction::class)->setName('list_praticiens');

    return $app;
};
