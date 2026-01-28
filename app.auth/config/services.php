<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\core\application\ports\AuthRepositoryInterface;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\infra\repositories\PDOAuthRepository;
use toubilib\api\actions\AuthLoginAction;
use toubilib\api\services\JWTService;
use toubilib\api\services\HATEOASService;
use toubilib\core\application\ports\AuthProviderInterface;
use toubilib\api\services\JWTAuthProvider;

return [
    // Connexions PDO strictement nécessaires
    'pdo.patient' => fn() => new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s', $_ENV['pat.host'], $_ENV['pat.port'] ?? 5432, $_ENV['pat.database']),
        $_ENV['pat.username'], $_ENV['pat.password']
    ),

    'pdo.auth' => fn() => new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s', $_ENV['auth.host'], $_ENV['auth.port'] ?? 5432, $_ENV['auth.database']),
        $_ENV['auth.username'], $_ENV['auth.password']
    ),

    // Repositories
    PatientRepositoryInterface::class =>
        fn(ContainerInterface $c) => new PDOPatientRepository($c->get('pdo.patient')),

    AuthRepositoryInterface::class =>
        fn(ContainerInterface $c) => new PDOAuthRepository($c->get('pdo.auth')),

    // Services Auth
    ServiceAuthInterface::class =>
        fn(ContainerInterface $c) => new ServiceAuth(
            $c->get(AuthRepositoryInterface::class),
            $c->get(PatientRepositoryInterface::class)
        ),

    JWTService::class => fn() => new JWTService(),
    HATEOASService::class => fn() => new HATEOASService(),

    AuthLoginAction::class =>
        fn(ContainerInterface $c) => new AuthLoginAction(
            $c->get(ServiceAuthInterface::class),
            $c->get(JWTService::class),
            $c->get(HATEOASService::class)
        ),
    AuthProviderInterface::class => fn(ContainerInterface $c) => new JWTAuthProvider()
];