<?php

// Auth manager
$container['auth'] = function ($container) {
    return new Auth();
};

// Flash messages
$container['flash'] = function ($container) {
    return new \Util\Messages();
};

// Sanitizing methods
$container['filter'] = function ($container) {
    return new \Middlewares\FilterMiddleware($container);
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
    $twig = new \Slim\Views\Twig('resources/views/twig', [
        'cache' => false, //DOCROOT.'/cache/twig',
    ]);

    // Aggiunta supporto moduli
    $loader = $twig->getLoader();
    $namespaces = require DOCROOT.'/config/namespaces.php';
    foreach ($namespaces as $path => $namespace) {
        $name = basename($path);
        $path = $path.'/views';

        if (file_exists($path)) {
            $loader->addPath($path, $name);
        }
    }

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $twig->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    $twig->offsetSet('config', $container['config']);
    $twig->offsetSet('auth', $container['auth']);
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

    if (!empty($container['debugbar'])) {
        $twig->offsetSet('debugbar', $container['debugbar']);
    }

    return $twig;
};

// Exception handlers
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['twig']->render($response, 'errors/404.twig');
    };
};
