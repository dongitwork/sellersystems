<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use App\Core\Application;
use App\Config\Database;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Create Container with PHP-DI
$containerBuilder = new DI\ContainerBuilder();

// Add container definitions
$containerBuilder->addDefinitions([
    'db' => function() {
        return Database::getInstance();
    },
    'view' => function() {
        return new \App\Core\View();
    },
    // Auto-wire controllers with container
    \Modules\Auth\Controllers\AuthController::class => DI\autowire()
        ->constructor(DI\get(DI\Container::class)),
    \Modules\Dashboard\Controllers\DashboardController::class => DI\autowire()
        ->constructor(DI\get(DI\Container::class)),
    \Modules\User\Controllers\UserController::class => DI\autowire()
        ->constructor(DI\get(DI\Container::class))
]);

$container = $containerBuilder->build();

// Set container to app factory
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Load Modules
$application = new Application($app, $container);
$application->loadModules();

// Run
$app->run();