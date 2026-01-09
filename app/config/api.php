<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\AgendaPraticienAction;
use toubilib\api\actions\ListPraticiensAction;
use toubilib\api\actions\RecherchePraticiensAction;
use toubilib\api\actions\RecherchePraticiensSpeVilleAction;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\actions\ListRDVOccupesAction;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\usecases\ServiceRDV;
use toubilib\api\actions\GetRDVAction;
use toubilib\api\actions\CreateRDVAction;
use toubilib\api\actions\GetPatientAction;
use toubilib\api\actions\GetConsultationsPatientAction;
use toubilib\api\actions\RegisterPatientAction;
use toubilib\api\middlewares\RegisterPatientInputDataValidationMiddleware;
use toubilib\core\application\ports\RDVRepositoryInterface;
use toubilib\core\application\ports\AuthRepositoryInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\infra\repositories\PDORDVRepository;
use toubilib\core\application\usecases\ServicePatient;
use toubilib\core\application\usecases\ServicePatientInterface;
use toubilib\api\services\HATEOASService;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;
use toubilib\api\actions\CreateIndisponibiliteAction;
use toubilib\api\actions\ListIndisponibilitesAction;
use toubilib\api\actions\DeleteIndisponibiliteAction;
use toubilib\api\middlewares\IndisponibiliteInputDataValidationMiddleware;
use toubilib\api\middlewares\AuthZPraticienIndisponibiliteMiddleware;

return [

    // Définition de PDO pour RDV
    'pdo.rdv' => fn() => new PDO(
        sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $_ENV['rdv.host'],
            $_ENV['rdv.port'] ?? 5432,
            $_ENV['rdv.database']
        ),
        $_ENV['rdv.username'],
        $_ENV['rdv.password']
    ),

    // Repository RDV
    RDVRepositoryInterface::class => fn(ContainerInterface $c) =>
        new PDORDVRepository($c->get('pdo.rdv')),

    // Service RDV
    ServiceRDVInterface::class => function(ContainerInterface $c) {
        return new ServiceRDV(
            $c->get(RDVRepositoryInterface::class),
            $c->get(ServicePraticienInterface::class),
            $c->get(ServicePatientInterface::class),
            $c->get(ServiceIndisponibiliteInterface::class)
        );
    },

    // Actions RDV
    ListRDVOccupesAction::class => fn(ContainerInterface $c) =>
        new ListRDVOccupesAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    GetRDVAction::class => fn(ContainerInterface $c) =>
        new GetRDVAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    CreateRDVAction::class => fn(ContainerInterface $c) =>
        new CreateRDVAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),
    
    GetConsultationsPatientAction::class => fn(ContainerInterface $c) =>
        new GetConsultationsPatientAction($c->get(ServiceRDVInterface::class), $c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),

    // Actions praticiens
    ListPraticiensAction::class => fn(ContainerInterface $c) =>
        new ListPraticiensAction($c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),

    RecherchePraticiensAction::class => fn(ContainerInterface $c) =>
        new RecherchePraticiensAction($c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),
    
    RecherchePraticiensSpeVilleAction::class => fn(ContainerInterface $c) =>
        new RecherchePraticiensSpeVilleAction($c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),

    AgendaPraticienAction::class => fn(ContainerInterface $c) =>
        new AgendaPraticienAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    // Actions patients
    GetPatientAction::class => fn(ContainerInterface $c) =>
        new GetPatientAction($c->get(ServicePatientInterface::class), $c->get(HATEOASService::class)),

    // Feature 12: Inscription patient
    RegisterPatientAction::class => fn(ContainerInterface $c) =>
        new RegisterPatientAction($c->get(ServiceAuthInterface::class), $c->get(HATEOASService::class)),

    RegisterPatientInputDataValidationMiddleware::class => fn(ContainerInterface $c) =>
        new RegisterPatientInputDataValidationMiddleware($c->get(AuthRepositoryInterface::class)),

    // Feature 13: Indisponibilités
    CreateIndisponibiliteAction::class => fn(ContainerInterface $c) =>
        new CreateIndisponibiliteAction($c->get(ServiceIndisponibiliteInterface::class), $c->get(HATEOASService::class)),

    ListIndisponibilitesAction::class => fn(ContainerInterface $c) =>
        new ListIndisponibilitesAction($c->get(ServiceIndisponibiliteInterface::class), $c->get(HATEOASService::class)),

    DeleteIndisponibiliteAction::class => fn(ContainerInterface $c) =>
        new DeleteIndisponibiliteAction($c->get(ServiceIndisponibiliteInterface::class), $c->get(HATEOASService::class)),

    IndisponibiliteInputDataValidationMiddleware::class => fn() =>
        new IndisponibiliteInputDataValidationMiddleware(),

    AuthZPraticienIndisponibiliteMiddleware::class => fn() =>
        new AuthZPraticienIndisponibiliteMiddleware(),

];
