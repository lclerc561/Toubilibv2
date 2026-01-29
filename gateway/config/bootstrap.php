<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use toubilib\gateway\api\middlewares\CORSMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

// Charger le fichier .env s'il existe
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$containerBuilder = new ContainerBuilder();

// Désactivation de l'autowire comme demandé par l'énoncé
$containerBuilder->useAutowiring(false);

// Chargement des définitions depuis les fichiers
$containerBuilder->addDefinitions(__DIR__ . '/settings.php');
$containerBuilder->addDefinitions(__DIR__ . '/services.php');

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Ajouter le middleware CORS globalement
$app->add(new CORSMiddleware());

$app->addErrorMiddleware(
    $container->get('displayErrorDetails'), 
    true, 
    true
)->getDefaultErrorHandler()->forceContentType('application/json');

$app = (require_once __DIR__ . '/../src/api/routes.php')($app);

return $app;