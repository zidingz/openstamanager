<?php

// Auth manager
$container['auth'] = function () {
    return new Auth();
};

// Flash messages
$container['flash'] = function () {
    return new \Util\Messages();
};

// Sanitizing methods
$container['filter'] = function ($container) {
    return new \Middlewares\FilterMiddleware($container);
};

// Custom router
$container['router'] = function () {
    return new Router();
};

use Slim\Views\PhpRenderer;

// Templating PHP
$container['view'] = function ($container) {
    $renderer = new PhpRenderer('./');

    $renderer->setAttributes([
        'database' => $container['database'],
        'dbo' => $container['database'],
        'config' => $container['config'],
        'router' => $container['router'],

        'rootdir' => ROOTDIR,
        'docroot' => DOCROOT,
        'baseurl' => BASEURL,
    ]);

    if (!empty($container['debugbar'])) {
        $renderer->addAttribute('debugbar', $container['debugbar']);
    }

    return $renderer;
};

// Templating Twig
$container['twig'] = function ($container) {
    $twig = new \Slim\Views\Twig(__DIR__.'/../resources/views/twig', [
        'cache' => false,
        'debug' => true,
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $twig->addExtension(new \Slim\Views\TwigExtension($router, $container->get('uri')));

    $twig->offsetSet('auth', $container['auth']);
    $twig->offsetSet('user', $container['auth']->user());
    $twig->offsetSet('flash', $container['flash']);
    $twig->offsetSet('router', $container['router']);

    $filter = new \Twig\TwigFilter('diffForHumans', 'diffForHumans');
    $twig->getEnvironment()->addFilter($filter);

    $function = new \Twig\TwigFunction('setting', 'setting');
    $twig->getEnvironment()->addFunction($function);

    $function = new \Twig\TwigFunction('searchFieldName', 'searchFieldName');
    $twig->getEnvironment()->addFunction($function);

    $function = new \Twig\TwigFunction('module_link', '\Modules::link');
    $twig->getEnvironment()->addFunction($function);

    $function = new \Twig\TwigFunction('module', '\Modules::get');
    $twig->getEnvironment()->addFunction($function);

    $twig->getEnvironment()->addExtension(new \Twig\Extension\DebugExtension());

    if (!empty($container['debugbar'])) {
        $twig->offsetSet('debugbar', $container['debugbar']);
    }

    return $twig;
};

// Exception handlers
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $response = $response->withStatus(404);

        return $container['twig']->render($response, 'errors/404.twig');
    };
};

$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $response = $response->withStatus(403);

        return $container['twig']->render($response, 'errors/403.twig');
    };
};

if (!$container['debug']) {
    $container['errorHandler'] = function ($container) {
        return function ($request, $response, $exception) use ($container) {
            $response = $response->withStatus(500);

            // Log the exception
            $container['logger']->logException($exception);

            return $container['twig']->render($response, 'errors/500.twig');
        };
    };

    $container['phpErrorHandler'] = function ($container) {
        return $container['errorHandler'];
    };
}
