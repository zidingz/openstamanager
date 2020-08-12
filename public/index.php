<?php

use DI\Container;
use Middlewares\Authorization\UserMiddleware;
use Modules\Module;
use Prints\Template;
use Slim\Factory\AppFactory;
use Widgets\Widget;

// Rimozione header X-Powered-By
header_remove('X-Powered-By');

ini_set('session.cookie_samesite', 'strict');

// Impostazioni di configurazione PHP
date_default_timezone_set('Europe/Rome');

// Disabilita i messaggi nativi di PHP
ini_set('display_startup_errors', 0);
ini_set('display_errors', 0);
// Ignora gli avvertimenti e le informazioni relative alla deprecazione di componenti
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_DEPRECATED & ~E_STRICT);

// Controllo sulla versione PHP
$minimum = '7.2.5';
if (version_compare(phpversion(), $minimum) < 0) {
    echo '
<p>Stai utilizzando la versione PHP '.phpversion().', non compatibile con OpenSTAManager.</p>

<p>Aggiorna PHP alla versione >= '.$minimum.'.</p>';
    exit();
}

// Caricamento delle dipendenze e delle librerie del progetto
$loader = require_once __DIR__.'/../vendor/autoload.php';

// Caricamento dei namespace predefiniti (retro-compatibilità)
$namespaces = require_once __DIR__.'/../config/namespaces.php';
foreach ($namespaces as $path => $namespace) {
    $loader->addPsr4($namespace.'\\', __DIR__.'/../'.$path.'/custom/src');
    $loader->addPsr4($namespace.'\\', __DIR__.'/../'.$path.'/src');
}

// Inizializzazione Dependency Injection
$container = new Container();
App::setContainer($container);

// Creazione dell'applicazione Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('response_factory', $app->getResponseFactory());
$container->set('router', $app->getRouteCollector()->getRouteParser());

// Impostazione dell'URL di base del software
$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a'.$_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }

    return '';
})());

// Registrazione globale dei percorsi di base (retro-compatibilità)
define('DOCROOT', realpath(__DIR__.'/..'));
define('ROOTDIR', $app->getBasePath());
define('BASEURL', (isHTTPS(true) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].ROOTDIR);

// Creazione della sessione
ini_set('session.use_trans_sid', '0');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params(0, $app->getBasePath());
session_cache_limiter(false);
session_start();

// Generazione delle dipendenze
require __DIR__.'/../config/dependencies.php';

// Aggiunta dei percorsi
require __DIR__.'/../routes/web.php';

// Aggiunta dei middleware
require __DIR__.'/../config/middlewares.php';

// Inizializzazione dei componenti del software
if (Update::isCoreUpdated()) {
    $modules = Module::getAll();
    $widgets = Widget::all();
    $prints = Template::all();

    // Iterazione per i singoli componenti
    $components = collect([$modules, $widgets])->flatten();
    foreach ($components as $component) {
        // Inizializzazione del componente
        $class = $component->getManager();
        $class->boot($app);

        // Registrazione degli aggiornamenti del componente
        Update::addComponentUpdates($class->updates());
    }
}

// Retro-compatibilità per i percorsi
$app->map(['GET', 'POST'], '[/{path:.*}]', 'Controllers\RetroController:index')
    ->add(UserMiddleware::class);

// Configurazione del templating personalizzato
$config = $container->get('config');
if (!empty($config['HTMLWrapper'])) {
    HTMLBuilder\HTMLBuilder::setWrapper($config['HTMLWrapper']);
}

foreach ((array) $config['HTMLHandlers'] as $key => $value) {
    HTMLBuilder\HTMLBuilder::setHandler($key, $value);
}

foreach ((array) $config['HTMLManagers'] as $key => $value) {
    HTMLBuilder\HTMLBuilder::setManager($key, $value);
}

// Run application
$app->run();
