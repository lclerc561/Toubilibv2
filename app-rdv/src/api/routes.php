<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\ListRDVOccupesAction;
use toubilib\api\actions\GetRDVAction;
use toubilib\api\actions\CreateRDVAction;
use toubilib\api\actions\AnnulerRDVAction;
use toubilib\api\actions\MarquerRDVHonoreAction;
use toubilib\api\actions\MarquerRDVNonHonoreAction;
use toubilib\api\actions\AgendaPraticienAction;
use toubilib\api\actions\GetPatientAction;
use toubilib\api\actions\GetConsultationsPatientAction;
use toubilib\api\actions\HomeAction;
use toubilib\api\middlewares\RDVInputDataValidationMiddleware;

return function(App $app): App {
    
    // Route home
    $app->get('/', HomeAction::class)->setName('home');

    // Routes RDV (sans authentification pour simplifier)
    $app->get('/praticiens/{id}/rdvs/occupes', ListRDVOccupesAction::class)
        ->setName('list_rdv_occupes');

    $app->get('/praticiens/{id}/agenda', AgendaPraticienAction::class)
        ->setName('agenda_praticien');

    $app->get('/rdvs/{id}', GetRDVAction::class)
        ->setName('get_rdv');

    $app->post('/rdvs', CreateRDVAction::class)
        ->add(RDVInputDataValidationMiddleware::class)
        ->setName('create_rdv');

    $app->delete('/rdvs/{id}/annuler', AnnulerRDVAction::class)
        ->setName('annuler_rdv');

    $app->patch('/rdvs/{id}/honorer', MarquerRDVHonoreAction::class)
        ->setName('marquer_rdv_honore');

    $app->patch('/rdvs/{id}/non-honorer', MarquerRDVNonHonoreAction::class)
        ->setName('marquer_rdv_non_honore');

    // Routes patients
    $app->get('/patients/{id}', GetPatientAction::class)
        ->setName('get_patient');

    $app->get('/patients/{id}/consultations', GetConsultationsPatientAction::class)
        ->setName('get_consultations_patient');

    return $app;
};