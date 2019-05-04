<?php

// Auth manager
$container['auth'] = function ($container) {
    return new Auth();
};

// Language manager
$container['translator'] = function ($container) {
    $config = $container->settings['config'];

    $lang = !empty($config['lang']) ? $config['lang'] : 'it';
    $formatter = !empty($config['formatter']) ? $config['formatter'] : [];

    $translator = new Translator();
    $translator->addLocalePath(DOCROOT.'/resources/locale');
    $translator->addLocalePath(DOCROOT.'/modules/*/locale');

    $translator->setLocale($lang);

    return $translator;
};

// I18n manager
$container['formatter'] = function ($container) {
    $config = $container->settings['config'];
    $options = !empty($config['formatter']) ? $config['formatter'] : [];

    $formatter = new Intl\Formatter(
        $container['translator']->getCurrentLocale(),
        empty($options['timestamp']) ? 'd/m/Y H:i' : $options['timestamp'],
        empty($options['date']) ? 'd/m/Y' : $options['date'],
        empty($options['time']) ? 'H:i' : $options['time'],
        empty($options['number']) ? [
            'decimals' => ',',
            'thousands' => '.',
        ] : $options['number']
    );

    $formatter->setPrecision(auth()->check() ? setting('Cifre decimali per importi') : 2);

    return $formatter;
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
    $settings = $container->settings;

    $twig = new \Slim\Views\Twig('resources/views/twig', [
        'cache' => false, //DOCROOT.'/cache/twig',
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $twig->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    $twig->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension($container['translator']->getTranslator()));

    $twig->offsetSet('config', $container['config']);
    $twig->offsetSet('auth', $container['auth']);
    $twig->offsetSet('flash', $container['flash']);
    $twig->offsetSet('lang', $container['translator']->getCurrentLocale());
    $twig->offsetSet('router', $container['router']);

    $filter = new \Twig\TwigFilter('timestamp', 'timestampFormat');
    $twig->getEnvironment()->addFilter($filter);

    $filter = new \Twig\TwigFilter('date', 'dateFormat');
    $twig->getEnvironment()->addFilter($filter);

    $filter = new \Twig\TwigFilter('time', 'timeFormat');
    $twig->getEnvironment()->addFilter($filter);

    $filter = new \Twig\TwigFilter('money', 'moneyFormat');
    $twig->getEnvironment()->addFilter($filter);

    $filter = new \Twig\TwigFilter('diffForHumans', 'diffForHumans');
    $twig->getEnvironment()->addFilter($filter);

    $function = new \Twig\TwigFunction('setting', 'setting');
    $twig->getEnvironment()->addFunction($function);

    $function = new \Twig\TwigFunction('currency', 'currency');
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
