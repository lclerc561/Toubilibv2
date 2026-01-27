<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\RegisterPatientAction;
use toubilib\api\middlewares\RegisterPatientInputDataValidationMiddleware;
use toubilib\core\application\ports\AuthRepositoryInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\api\services\HATEOASService;
use toubilib\api\actions\AuthLoginAction;
use toubilib\api\services\JWTService;

return [
    // Feature 12: Inscription patient
    RegisterPatientAction::class => fn(ContainerInterface $c) =>
        new RegisterPatientAction($c->get(ServiceAuthInterface::class), $c->get(HATEOASService::class)),

    RegisterPatientInputDataValidationMiddleware::class => fn(ContainerInterface $c) =>
        new RegisterPatientInputDataValidationMiddleware($c->get(AuthRepositoryInterface::class)),

    // Login
    AuthLoginAction::class =>
        fn(ContainerInterface $c) => new AuthLoginAction(
            $c->get(ServiceAuthInterface::class),
            $c->get(JWTService::class),
            $c->get(HATEOASService::class)
        )
];