<?php

use Middlewares\CalendarMiddleware;
use Middlewares\ConfigMiddleware;
use Middlewares\ContentMiddleware;
use Middlewares\CSRFMiddleware;
use Middlewares\HTMLMiddleware;
use Middlewares\LangMiddleware;
use Slim\Views\TwigMiddleware;

$app->add(new CalendarMiddleware($container));

// Middleware per i contenuti di base
$app->add(new ContentMiddleware($container));

// Middleware per l'input
$app->add($container->get('filter'));

$app->add(new ConfigMiddleware($container));

$app->addRoutingMiddleware();
$app->add(TwigMiddleware::createFromContainer($app, 'twig'));

// Middleware per la lingua
$app->add(new LangMiddleware($container));

// Middleware per HTML semplificato
$app->add(new HTMLMiddleware($container));

// Middleware CSRF
//$app->add(new CSRFMiddleware($container));

// Middleware di gestione errori
$error_middleware = $app->addErrorMiddleware(true, true, true);
$default_handler = $error_middleware->getDefaultErrorHandler();

$logger = $container->get('logger');
$logger->setDebugHandler($default_handler);
$error_middleware->setDefaultErrorHandler($logger);
