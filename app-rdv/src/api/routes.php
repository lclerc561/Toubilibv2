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

// Importation des middlewares d'authentification et d'autorisation
use toubilib\api\middlewares\AuthNMiddleware;
use toubilib\api\middlewares\AuthZRDVMiddleware;
use toubilib\api\middlewares\AuthZPatientMiddleware;
use toubilib\api\middlewares\AuthZPraticienAgendaMiddleware;
use toubilib\api\middlewares\AuthZPraticienRDVMiddleware;

return function(App $app): App {
    
    // Route home (Publique)
    $app->get('/', HomeAction::class)->setName('home');

    // --- Routes RDV ---

    // Route publique (liste des créneaux occupés)
    $app->get('/praticiens/{id}/rdvs/occupes', ListRDVOccupesAction::class)
        ->setName('list_rdv_occupes');

    // Routes protégées - AuthN validera le token, AuthZ vérifiera les permissions
    $app->get('/praticiens/{id}/agenda', AgendaPraticienAction::class)
        ->add(AuthZPraticienAgendaMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('agenda_praticien');

    $app->get('/rdvs/{id}', GetRDVAction::class)
        ->add(AuthZRDVMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('get_rdv');

    $app->post('/rdvs', CreateRDVAction::class)
        ->add(RDVInputDataValidationMiddleware::class)
        ->add(AuthZPatientMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('create_rdv');

    $app->delete('/rdvs/{id}/annuler', AnnulerRDVAction::class)
        ->add(AuthZRDVMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('annuler_rdv');

    $app->patch('/rdvs/{id}/honorer', MarquerRDVHonoreAction::class)
        ->add(AuthZPraticienRDVMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('marquer_rdv_honore');

    $app->patch('/rdvs/{id}/non-honorer', MarquerRDVNonHonoreAction::class)
        ->add(AuthZPraticienRDVMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('marquer_rdv_non_honore');

    // --- Routes Patients ---

    $app->get('/patients/{id}', GetPatientAction::class)
        ->add(AuthZPatientMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('get_patient');

    $app->get('/patients/{id}/consultations', GetConsultationsPatientAction::class)
        ->add(AuthZPatientMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('get_consultations_patient');

    return $app;
};