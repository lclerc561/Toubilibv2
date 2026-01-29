<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\core\application\usecases\ServicePatient;
use toubilib\core\application\usecases\ServicePatientInterface;
use toubilib\core\application\ports\RDVRepositoryInterface;
use toubilib\infra\repositories\PDORDVRepository;
use toubilib\core\application\usecases\ServiceRDV;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\api\middlewares\CORSMiddleware;
use toubilib\api\services\HATEOASService;
use toubilib\infra\adapters\PraticienServiceAdapter;
use GuzzleHttp\Client;

return [
    // Connexions PDO
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

    'pdo.patient' => fn() => new PDO(
        sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $_ENV['pat.host'],
            $_ENV['pat.port'] ?? 5432,
            $_ENV['pat.database']
        ),
        $_ENV['pat.username'],
        $_ENV['pat.password']
    ),

    // Repositories
    RDVRepositoryInterface::class =>
        fn(ContainerInterface $c) => new PDORDVRepository($c->get('pdo.rdv')),

    PatientRepositoryInterface::class =>
        fn(ContainerInterface $c) => new PDOPatientRepository($c->get('pdo.patient')),

    // Client Guzzle pour appeler le microservice praticiens
    'client.praticiens' => function () {
        $baseUri = $_ENV['API_PRATICIENS_URL'] ?? 'http://app.praticiens:80';
        return new Client([
            'base_uri' => $baseUri,
            'timeout' => 30.0,
        ]);
    },

    // Adaptateur pour communiquer avec le microservice praticiens
    PraticienServiceAdapter::class => fn(ContainerInterface $c) =>
        new PraticienServiceAdapter($c->get('client.praticiens')),

    // Services
    ServicePatientInterface::class =>
        fn(ContainerInterface $c) => new ServicePatient($c->get(PatientRepositoryInterface::class)),

    ServiceRDVInterface::class =>
        fn(ContainerInterface $c) => new ServiceRDV(
            $c->get(RDVRepositoryInterface::class),
            $c->get(PraticienServiceAdapter::class),  // Utilise l'adaptateur HTTP
            $c->get(ServicePatientInterface::class)
        ),

    // Middlewares
    CORSMiddleware::class => fn() => new CORSMiddleware(),

    // Services
    HATEOASService::class => fn() => new HATEOASService(),
];