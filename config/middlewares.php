<?php

use Middlewares\CalendarMiddleware;
use Middlewares\ConfigMiddleware;
use Middlewares\ContentMiddleware;
use Middlewares\CSRFMiddleware;
use Middlewares\LangMiddleware;
use Slim\Views\TwigMiddleware;

$app->add(TwigMiddleware::createFromContainer($app, 'twig'));

$app->add(new CalendarMiddleware($container));

// Middleware per i contenuti di base
$app->add(new ContentMiddleware($container));

// Middleware per l'input
$app->add($container->get('filter'));

$app->add(new ConfigMiddleware($container));

// Middleware per la lingua
$app->add(new LangMiddleware($container));

// Middleware CSRF
//$app->add(new CSRFMiddleware($container));
