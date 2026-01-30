<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\ListPraticiensAction;
use toubilib\api\actions\RecherchePraticiensAction;
use toubilib\api\actions\RecherchePraticiensSpeVilleAction;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\services\HATEOASService;

return [
    // Actions praticiens
    ListPraticiensAction::class => fn(ContainerInterface $c) =>
        new ListPraticiensAction($c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),

    RecherchePraticiensAction::class => fn(ContainerInterface $c) =>
        new RecherchePraticiensAction($c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),
    
    RecherchePraticiensSpeVilleAction::class => fn(ContainerInterface $c) =>
        new RecherchePraticiensSpeVilleAction($c->get(ServicePraticienInterface::class), $c->get(HATEOASService::class)),
];