<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\ListPraticiensAction;
use toubilib\api\actions\RecherchePraticiensAction;
use toubilib\api\actions\RecherchePraticiensSpeVilleAction;
use toubilib\api\actions\HomeAction;

return function(App $app): App {
    
    // Route home
    $app->get('/', HomeAction::class)->setName('home');

    // Routes praticiens (sans authentification)
    $app->get('/praticiens', ListPraticiensAction::class)->setName('list_praticiens');

    $app->get('/praticiens/search', RecherchePraticiensSpeVilleAction::class)
        ->setName('recherche_praticiens_filter');

    $app->get('/praticiens/{id}', RecherchePraticiensAction::class)->setName('recherche_praticien');

    return $app;
};