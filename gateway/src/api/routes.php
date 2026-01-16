<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\api\actions\ListPraticiensAction;
use toubilib\gateway\api\actions\DetailPraticien;

return function(App $app): App {
    // Route pour accéder à la liste des praticiens
    $app->get('/praticiens', ListPraticiensAction::class)->setName('list_praticiens');
    // Route pour accéder aux détails d'un praticien
    $app->get('/praticiens/{id}', DetailPraticien::class)->setName('detail_praticien');

    return $app;
};
