<?php
declare(strict_types=1);

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\ListPraticiensAction;
use toubilib\api\actions\RecherchePraticiensAction;
use toubilib\api\actions\RecherchePraticiensSpeVilleAction;
use toubilib\api\actions\ListRDVOccupesAction;
use toubilib\api\actions\HomeAction;
use toubilib\api\actions\CreateRDVAction;
use toubilib\api\middlewares\RDVInputDataValidationMiddleware;
use toubilib\api\middlewares\AuthInputDataValidationMiddleware;
use toubilib\api\middlewares\AuthNMiddleware;
use toubilib\api\middlewares\AuthZPatientMiddleware;
use toubilib\api\middlewares\AuthZPraticienMiddleware;
use toubilib\api\middlewares\AuthZRDVMiddleware;
use toubilib\api\middlewares\AuthZPraticienAgendaMiddleware;
use toubilib\api\middlewares\AuthZPraticienRDVMiddleware;
use toubilib\api\actions\AnnulerRDVAction;
use toubilib\api\actions\GetPatientAction;
use toubilib\api\actions\MarquerRDVHonoreAction;
use toubilib\api\actions\MarquerRDVNonHonoreAction;
use toubilib\api\actions\GetConsultationsPatientAction;
use toubilib\api\actions\RegisterPatientAction;
use toubilib\api\middlewares\RegisterPatientInputDataValidationMiddleware;
use toubilib\api\actions\CreateIndisponibiliteAction;
use toubilib\api\actions\ListIndisponibilitesAction;
use toubilib\api\actions\DeleteIndisponibiliteAction;
use toubilib\api\middlewares\IndisponibiliteInputDataValidationMiddleware;
use toubilib\api\middlewares\AuthZPraticienIndisponibiliteMiddleware;



return function( \Slim\App $app):\Slim\App {

    $app->get('/', HomeAction::class)->setName('home');

    $app->post('/auth/login', \toubilib\api\actions\AuthLoginAction::class)
        ->add(AuthInputDataValidationMiddleware::class)
        ->setName('auth_login');

    // Feature 12: S'inscrire en tant que patient
    $app->post('/auth/register', RegisterPatientAction::class)
        ->add(RegisterPatientInputDataValidationMiddleware::class)
        ->setName('register_patient');

    $app->get('/praticiens', ListPraticiensAction::class)->setName('list_praticiens');

    // feature 9 : recherche praticiens par spécialité et/ou ville
    $app->get('/praticiens/search', RecherchePraticiensSpeVilleAction::class)
        ->setName('recherche_praticiens_filter');

    $app->get('/praticiens/{id}', RecherchePraticiensAction::class)->setName('recherche_praticien');

    $app->get('/praticiens/{id}/rdvs/occupes', ListRDVOccupesAction::class)->setName('list_rdv_occupes');

    // Opération 4: Consulter un RDV - praticien ou patient du RDV
    $app->get('/rdvs/{id}', \toubilib\api\actions\GetRDVAction::class)
        ->add(AuthZRDVMiddleware::class)
        ->add(AuthNMiddleware::class);

    // Opération 5: Réserver un RDV - patient uniquement
    $app->post('/rdvs', CreateRDVAction::class)
        ->add(RDVInputDataValidationMiddleware::class)
        ->add(AuthZPatientMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('create_rdv');
    
    // Opération 6: Annuler un RDV - praticien ou patient du RDV
    $app->delete('/rdvs/{id}/annuler', AnnulerRDVAction::class)
        ->add(AuthZRDVMiddleware::class)
        ->add(AuthNMiddleware::class);
    
    // Marquer un RDV comme honoré - praticien propriétaire uniquement
    $app->patch('/rdvs/{id}/honorer', MarquerRDVHonoreAction::class)
        ->add(AuthZPraticienRDVMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('marquer_rdv_honore');
    
    // Marquer un RDV comme non honoré - praticien propriétaire uniquement
    $app->patch('/rdvs/{id}/non-honorer', MarquerRDVNonHonoreAction::class)
        ->add(AuthZPraticienRDVMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('marquer_rdv_non_honore');
        
    // Opération 7: Afficher agenda praticien - praticien propriétaire
    $app->get('/praticiens/{id}/agenda', \toubilib\api\actions\AgendaPraticienAction::class)
        ->add(AuthZPraticienAgendaMiddleware::class)
        ->add(AuthNMiddleware::class);

    // Feature 13: Gérer les indisponibilités temporaires d'un praticien
    $app->post('/praticiens/{id}/indisponibilites', CreateIndisponibiliteAction::class)
        ->add(IndisponibiliteInputDataValidationMiddleware::class)
        ->add(AuthZPraticienIndisponibiliteMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('create_indisponibilite');

    $app->get('/praticiens/{id}/indisponibilites', ListIndisponibilitesAction::class)
        ->add(AuthZPraticienIndisponibiliteMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('list_indisponibilites');

    $app->delete('/praticiens/{id}/indisponibilites/{indisponibiliteId}', DeleteIndisponibiliteAction::class)
        ->add(AuthZPraticienIndisponibiliteMiddleware::class)
        ->add(AuthNMiddleware::class)
        ->setName('delete_indisponibilite');

    $app->get('/patients/{id}', GetPatientAction::class);

    // Opération &&: Afficher historique des consultations d'un patient
    $app->get('/patients/{id}/consultations', GetConsultationsPatientAction::class)
    ->add(AuthZPatientMiddleware::class)
    ->add(AuthNMiddleware::class);

    return $app;
};