<?php

use Monolog\Handler\FilterHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

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

// Logger
$container['logger'] = function ($container) {
    $logger = new Monolog\Logger('Logs');
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushProcessor(new Monolog\Processor\WebProcessor());

    $handlers = [];
    // File di log di base (logs/error.log, logs/setup.log)
    $handlers[] = new StreamHandler(__DIR__.'/../logs/error.log', Monolog\Logger::ERROR);

    // File di log ordinati in base alla data
    if ($container['debug']) {
        $handlers[] = new RotatingFileHandler(__DIR__.'/../logs/error.log', 0, Monolog\Logger::ERROR);
    }

    $pattern = '[%datetime%] %channel%.%level_name%: %message% %context%'.PHP_EOL.'%extra% '.PHP_EOL;
    $monologFormatter = new Monolog\Formatter\LineFormatter($pattern);
    $monologFormatter->includeStacktraces($container['debug']);

    // Filtra gli errori per livello preciso del gestore dedicato
    foreach ($handlers as $handler) {
        $handler->setFormatter($monologFormatter);
        $logger->pushHandler(new FilterHandler($handler, [$handler->getLevel()]));
    }

    return $logger;
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
        'debug' => true,
    ]);

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

            // Log the message
            $container['logger']->addError($exception->getMessage(), [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return $container['twig']->render($response, 'errors/500.twig');
        };
    };

    $container['phpErrorHandler'] = function ($container) {
        return $container['errorHandler'];
    };
}
