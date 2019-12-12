<?php

use DI\Container;
use Models\Module;
use Slim\Factory\AppFactory;

// Impostazioni di configurazione PHP
date_default_timezone_set('Europe/Rome');

// Disabilita i messaggi nativi di PHP
ini_set('display_startup_errors', 0);
ini_set('display_errors', 0);
// Ignora gli avvertimenti e le informazioni relative alla deprecazione di componenti
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_DEPRECATED & ~E_STRICT);

// Controllo sulla versione PHP
$minimum = '7.2.0';
if (version_compare(phpversion(), $minimum) < 0) {
    echo '
<p>Stai utilizzando la versione PHP '.phpversion().', non compatibile con OpenSTAManager.</p>

<p>Aggiorna PHP alla versione >= '.$minimum.'.</p>';
    exit();
}

// Caricamento delle dipendenze e delle librerie del progetto
$loader = require_once __DIR__.'/../vendor/autoload.php';

$namespaces = require_once __DIR__.'/../config/namespaces.php';
foreach ($namespaces as $path => $namespace) {
    $loader->addPsr4($namespace.'\\', __DIR__.'/../'.$path.'/custom/src');
    $loader->addPsr4($namespace.'\\', __DIR__.'/../'.$path.'/src');
}

// Inizializzazione Dependency Injection
$container = new Container();
App::setContainer($container);

// Istanziamento dell'applicazione Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

$container->set('response_factory', $app->getResponseFactory());
$container->set('router', $app->getRouteCollector()->getRouteParser());

// Impostazione percorso di base
$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a'.$_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }

    return '';
})());

// Individuazione dei percorsi di base
define('DOCROOT', __DIR__.'/..');
define('ROOTDIR', $app->getBasePath());
define('BASEURL', (isHTTPS(true) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].ROOTDIR);

// Istanziamento della sessione
ini_set('session.use_trans_sid', '0');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params(0, $app->getBasePath());
session_cache_limiter(false);
session_start();

// Istanziamento delle dipendenze
require __DIR__.'/../config/dependencies.php';

// Aggiunta dei percorsi
require __DIR__.'/../routes/web.php';

// Aggiunta dei middleware
require __DIR__.'/../config/middlewares.php';

// Inizializzazione percorsi per i moduli
if (Update::isCoreUpdated()) {
    $modules = Module::withoutGlobalScope('enabled')
        ->get();
    foreach ($modules as $module) {
        $class = $module->manager;
        $class->boot($app, $module);

        Update::addModuleUpdates($class->updates());
    }
}

// Configurazione templating personalizzato
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
