<?php
declare(strict_types=1);

use Slim\App;
use toubilib\api\actions\AuthLoginAction;
use toubilib\api\actions\RegisterPatientAction;
use toubilib\api\middlewares\AuthInputDataValidationMiddleware;
use toubilib\api\middlewares\RegisterPatientInputDataValidationMiddleware;

return function(App $app): App {

    //connexion
    $app->post('/login', AuthLoginAction::class)
    ->add(AuthInputDataValidationMiddleware::class);

    //inscription
    $app->post('/register', RegisterPatientAction::class)
    ->add(RegisterPatientInputDataValidationMiddleware::class);

    return $app;
};