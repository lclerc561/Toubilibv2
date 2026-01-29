<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\middlewares\CORSMiddleware;
use toubilib\api\services\HATEOASService;

return [
    // Connexion PDO praticien uniquement
    'pdo.praticien' => fn() => new PDO(
        sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $_ENV['prat.host'],
            $_ENV['prat.port'] ?? 5432,
            $_ENV['prat.database']
        ),
        $_ENV['prat.username'],
        $_ENV['prat.password']
    ),

    // Repository praticien
    PraticienRepositoryInterface::class =>
        fn(ContainerInterface $c) => new PDOPraticienRepository($c->get('pdo.praticien')),

    // Service praticien
    ServicePraticienInterface::class =>
        fn(ContainerInterface $c) => new ServicePraticien($c->get(PraticienRepositoryInterface::class)),

    // Middlewares
    CORSMiddleware::class => fn() => new CORSMiddleware(),

    // Services
    HATEOASService::class => fn() => new HATEOASService(),
];