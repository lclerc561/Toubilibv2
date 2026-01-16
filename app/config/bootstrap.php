<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$containerBuilder = new ContainerBuilder();

// Désactivation de l'autowire comme demandé par l'énoncé
$containerBuilder->useAutowiring(false);

// Chargement des définitions depuis les trois fichiers
$containerBuilder->addDefinitions(__DIR__ . '/settings.php');
$containerBuilder->addDefinitions(__DIR__ . '/services.php');
$containerBuilder->addDefinitions(__DIR__ . '/api.php');

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->addErrorMiddleware(
    $container->get('displayErrorDetails'), 
    true, 
    true
)->getDefaultErrorHandler()->forceContentType('application/json');

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);

return $app;