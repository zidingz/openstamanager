<?php

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Twig\TwigFilter;
use Twig\TwigFunction;

// Configurazione di default
$container->set('default_config', function (ContainerInterface $container) {
    if (file_exists(DOCROOT.'/config.example.php')) {
        include DOCROOT.'/config.example.php';
    }

    $db_host = '';
    $db_username = '';
    $db_password = '';
    $db_name = '';
    $port = '';
    $lang = '';

    $formatter = [
        'timestamp' => 'd/m/Y H:i',
        'date' => 'd/m/Y',
        'time' => 'H:i',
        'number' => [
            'decimals' => ',',
            'thousands' => '.',
        ],
    ];

    return get_defined_vars();
});

// Configurazione
$container->set('config', function (ContainerInterface $container) {
    if (file_exists(DOCROOT.'/config.inc.php')) {
        include DOCROOT.'/config.inc.php';

        $config = get_defined_vars();
    } else {
        $config = [];
    }

    $defaultConfig = $container->get('default_config');

    $result = array_merge($defaultConfig, $config);

    // Operazioni di normalizzazione sulla configurazione
    $result['debug'] = !empty($result['debug']);
    $result['lang'] = $result['lang'] == 'it' ? 'it_IT' : $result['lang'];

    return $result;
});

// Debug
$container->set('debug', function (ContainerInterface $container) {
    return $container->get('config')['debug'];
});

// Logger
$logger = new Logger($container);
$container->set('logger', $logger);

// Auth manager
$container->set('auth', function (ContainerInterface $container) {
    return new Auth();
});

// Flash messages
$container->set('flash', function (ContainerInterface $container) {
    return new \Util\Messages();
});

// Sanitizing methods
$container->set('filter', function (ContainerInterface $container) {
    return new \Middlewares\FilterMiddleware($container);
});

// Database
$container->set('database', function (ContainerInterface $container) {
    $config = $container->get('config');

    $database = new Database($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);

    return $database;
});

// Debugbar
if ($container->get('debug')) {
    $container->set('debugbar', function (ContainerInterface $container) {
        $debugbar = new \DebugBar\StandardDebugBar();

        $debugbar->addCollector(new \Extensions\EloquentCollector($container->get('database')->getCapsule()));
        $debugbar->addCollector(new \DebugBar\Bridge\MonologCollector($container->get('logger')));

        $debugbarRenderer = $debugbar->getJavascriptRenderer();
        $debugbarRenderer->setIncludeVendors(false);
        $debugbarRenderer->setBaseUrl(BASEURL.'/assets/php-debugbar');

        return $debugbarRenderer;
    });
}

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
$container->set('twig', function (ContainerInterface $container) {
    $twig = new Twig(__DIR__.'/../resources/views', [
        'cache' => false,
        'debug' => $container->get('debug'),
    ]);

    $twig->offsetSet('auth', $container->get('auth'));
    $twig->offsetSet('user', $container->get('auth')->user());
    $twig->offsetSet('flash', $container->get('flash'));

    if ($container->has('debugbar')) {
        $twig->offsetSet('debugbar', $container->get('debugbar'));
    }

    $environment = $twig->getEnvironment();

    $filter = new TwigFilter('diffForHumans', 'diffForHumans');
    $environment->addFilter($filter);

    $function = new TwigFunction('setting', 'setting');
    $environment->addFunction($function);

    $function = new TwigFunction('searchFieldName', 'searchFieldName');
    $environment->addFunction($function);

    $function = new TwigFunction('module_link', '\Modules::link');
    $environment->addFunction($function);

    $function = new TwigFunction('module', '\Modules::get');
    $environment->addFunction($function);

    $environment->addExtension(new \Twig\Extension\DebugExtension());

    return $twig;
});
