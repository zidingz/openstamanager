<?php

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Slim\Views\PhpRenderer;
use Slim\Psr7\Response;

// Auth manager
$container->set('auth', function(ContainerInterface $container){
    return new Auth();
});

// Flash messages
$container->set('flash', function(ContainerInterface $container){
    return new \Util\Messages();
});

// Sanitizing methods
$container->set('filter', function(ContainerInterface $container){
    return new \Middlewares\FilterMiddleware($container);
});

// Logger
$container->set('logger', function(ContainerInterface $container){
    return new Logger($container);
});

// Database
$container->set('database', function(ContainerInterface $container){
    $config = $container->get('config');

    $database = new Database($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

    return $database;
});
/*
// Debugbar
if (App::debug()) {
    $debugbar = new \DebugBar\StandardDebugBar();

    $debugbar->addCollector(new \Extensions\EloquentCollector($container['database']->getCapsule()));
    $debugbar->addCollector(new \DebugBar\Bridge\MonologCollector($container['logger']));

    $paths = App::getPaths();
    $debugbarRenderer = $debugbar->getJavascriptRenderer();
    $debugbarRenderer->setIncludeVendors(false);
    $debugbarRenderer->setBaseUrl($paths['assets'].'/php-debugbar');

    $container['debugbar'] = $debugbarRenderer;
}*/

/*
// Templating PHP
$container->set('view', function(ContainerInterface $container){
    $renderer = new PhpRenderer('./');

    $renderer->setAttributes([
        'database' => $container->get('database'),
        'dbo' => $container->get('database'),
        'config' => $container->get('config'),
        'router' => $container->get('router'),

        'rootdir' => ROOTDIR,
        'docroot' => DOCROOT,
        'baseurl' => BASEURL,
    ]);

    if (!empty($container->get('debugbar'))) {
        $renderer->addAttribute('debugbar', $container->get('debugbar'));
    }

    return $renderer;
});
*/

// Templating Twig
$container->set('twig', function(ContainerInterface $container){
    $twig = new Twig(__DIR__.'/../resources/views/twig', [
        'cache' => false,
        'debug' => true,
    ]);

    $twig->offsetSet('auth', $container->get('auth'));
    $twig->offsetSet('user', $container->get('auth')->user());
    $twig->offsetSet('flash', $container->get('flash'));

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

    if ($container->has('debugbar')) {
        $twig->offsetSet('debugbar', $container->get('debugbar'));
    }

    return $twig;
});
/*
// Exception handlers
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new Response();
        $response = $response->withStatus(404);

        return $this->get('twig')->render($response, 'errors/404.twig');
    }
);

// Set the Not Allowed Handler
$errorMiddleware->setErrorHandler(
    HttpMethodNotAllowedException::class,
    function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new Response();
        $response = $response->withStatus(403);

        return $this->get('twig')->render($response, 'errors/403.twig');
    }
);



if (!$container->set('debug']) {
    $container->set('errorHandler'], function(ContainerInterface $container){
        return function ($request, $response, $exception) use ($container) {
            $response = $response->withStatus(500);

            // Log the exception
            $container->set('logger']->logException($exception);

            return $container->set('twig']->render($response, 'errors/500.twig');
        });
    });

    $container->set('phpErrorHandler'], function(ContainerInterface $container){
        return $container->set('errorHandler'];
    });
}
*/
