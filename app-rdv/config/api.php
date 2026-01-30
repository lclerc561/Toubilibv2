<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\ListRDVOccupesAction;
use toubilib\api\actions\GetRDVAction;
use toubilib\api\actions\CreateRDVAction;
use toubilib\api\actions\AnnulerRDVAction;
use toubilib\api\actions\MarquerRDVHonoreAction;
use toubilib\api\actions\MarquerRDVNonHonoreAction;
use toubilib\api\actions\AgendaPraticienAction;
use toubilib\api\actions\GetConsultationsPatientAction;
use toubilib\api\actions\GetPatientAction;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\usecases\ServicePatientInterface;
use toubilib\infra\adapters\PraticienServiceAdapter;
use toubilib\api\services\HATEOASService;

return [
    // Actions RDV
    ListRDVOccupesAction::class => fn(ContainerInterface $c) =>
        new ListRDVOccupesAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    GetRDVAction::class => fn(ContainerInterface $c) =>
        new GetRDVAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    CreateRDVAction::class => fn(ContainerInterface $c) =>
        new CreateRDVAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    AnnulerRDVAction::class => fn(ContainerInterface $c) =>
        new AnnulerRDVAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    MarquerRDVHonoreAction::class => fn(ContainerInterface $c) =>
        new MarquerRDVHonoreAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    MarquerRDVNonHonoreAction::class => fn(ContainerInterface $c) =>
        new MarquerRDVNonHonoreAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    AgendaPraticienAction::class => fn(ContainerInterface $c) =>
        new AgendaPraticienAction($c->get(ServiceRDVInterface::class), $c->get(HATEOASService::class)),

    // Actions Patient
    GetPatientAction::class => fn(ContainerInterface $c) =>
        new GetPatientAction($c->get(ServicePatientInterface::class), $c->get(HATEOASService::class)),

    GetConsultationsPatientAction::class => fn(ContainerInterface $c) =>
        new GetConsultationsPatientAction(
            $c->get(ServiceRDVInterface::class),
            $c->get(PraticienServiceAdapter::class),  // Injecter l'adaptateur
            $c->get(HATEOASService::class)
        ),
];