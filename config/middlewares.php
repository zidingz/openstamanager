<?php

use Middlewares\CalendarMiddleware;
use Middlewares\ConfigMiddleware;
use Middlewares\ContentMiddleware;
use Middlewares\CSRFMiddleware;
use Middlewares\HTMLMiddleware;
use Middlewares\LangMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\TwigMiddleware;

$app->add(new CalendarMiddleware($container));

// Middleware per i contenuti di base
$app->add(new ContentMiddleware($container));

/*
 * Salvataggio dell'URL come "referer" per un eventuale redirect alla pagine precedente.
 */
$app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $response = $handler->handle($request);
    $flash = $this->get('flash');

    // Individuazione dei percorsi
    $messages_url = $this->get('router')->urlFor('messages');
    $current_url = $request->getUri()->__toString();
    $previous_url = $flash->getFirstMessage('referer');

    // Gestione dell'URL precedente
    $url = strpos($previous_url, $messages_url) === false ? $previous_url : null;
    $is_auth = $this->get('auth')->isAuthenticated();
    if (empty($url) || ($is_auth && $response->getStatusCode() == 200)) {
        $url = strpos($current_url, $messages_url) === false ? $current_url : $url;
    }

    // Aggiornamento delle informazioni
    $flash->clearMessage('referer');
    $flash->addMessage('referer', $url);

    return $response;
});

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
