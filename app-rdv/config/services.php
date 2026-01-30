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
use toubilib\api\middlewares\AuthNMiddleware;
use toubilib\api\services\HATEOASService;
use toubilib\core\application\ports\PraticienInfoPort;
use toubilib\infra\adapters\PraticienServiceAdapter;
use toubilib\core\application\ports\AuthProviderInterface;
use toubilib\api\services\JWTAuthProvider;
use toubilib\core\domain\ports\EventPublisherInterface;
use toubilib\infra\messaging\AMQPEventPublisher;
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

    // Adaptateur pour communiquer avec le microservice praticiens (implémente le port)
    PraticienInfoPort::class => fn(ContainerInterface $c) =>
        new PraticienServiceAdapter($c->get('client.praticiens')),

    // Event Publisher
    EventPublisherInterface::class => fn() => new AMQPEventPublisher(),

    // Services
    ServicePatientInterface::class =>
        fn(ContainerInterface $c) => new ServicePatient($c->get(PatientRepositoryInterface::class)),

    ServiceRDVInterface::class =>
        fn(ContainerInterface $c) => new ServiceRDV(
            $c->get(RDVRepositoryInterface::class),
            $c->get(PraticienInfoPort::class),
            $c->get(ServicePatientInterface::class),
            $c->get(EventPublisherInterface::class)
        ),

    // Auth Provider pour validation JWT
    AuthProviderInterface::class => function() {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? throw new \Exception('JWT_SECRET non défini dans .env');
        return new JWTAuthProvider($jwtSecret);
    },

    // Middlewares
    CORSMiddleware::class => fn() => new CORSMiddleware(),
    AuthNMiddleware::class => fn(ContainerInterface $c) =>
        new AuthNMiddleware($c->get(AuthProviderInterface::class)),

    \toubilib\api\middlewares\AuthZRDVMiddleware::class => fn(ContainerInterface $c) =>
        new \toubilib\api\middlewares\AuthZRDVMiddleware($c->get(ServiceRDVInterface::class)),

    \toubilib\api\middlewares\AuthZPatientMiddleware::class => fn(ContainerInterface $c) =>
        new \toubilib\api\middlewares\AuthZPatientMiddleware($c->get(ServicePatientInterface::class)),

    \toubilib\api\middlewares\AuthZPraticienAgendaMiddleware::class => fn(ContainerInterface $c) =>
        new \toubilib\api\middlewares\AuthZPraticienAgendaMiddleware(),

    \toubilib\api\middlewares\AuthZPraticienMiddleware::class => fn(ContainerInterface $c) =>
        new \toubilib\api\middlewares\AuthZPraticienMiddleware(),

    \toubilib\api\middlewares\AuthZPraticienRDVMiddleware::class => fn(ContainerInterface $c) =>
        new \toubilib\api\middlewares\AuthZPraticienRDVMiddleware(),

    // Services
    HATEOASService::class => fn() => new HATEOASService(),
];