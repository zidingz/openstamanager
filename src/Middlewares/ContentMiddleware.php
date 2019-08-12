<?php

namespace Middlewares;

use Modules;
use Plugins;
use Update;
use Util\Query;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ContentMiddleware extends Middleware
{
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next)
    {
        $route = $request->getAttribute('route');
        if (!$route || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $next($request, $response);
        }

        $args = $route->getArguments();

        Modules::setCurrent($args['module_id']);
        Plugins::setCurrent($args['plugin_id']);

        Query::setModuleRecord($args['module_record_id']);

        // Variabili fondamentali
        $module = Modules::getCurrent();
        $plugin = Plugins::getCurrent();
        $structure = isset($plugin) ? $plugin : $module;

        $id_module = $module['id'];
        $id_plugin = $plugin['id'];

        $args['id_module'] = $id_module;
        $args['id_plugin'] = $id_plugin;
        $args['id_record'] = $args['record_id'];

        $args['structure'] = $structure;
        $args['plugin'] = $plugin;
        $args['module'] = $module;

        $user = auth()->getUser();
        $args['user'] = $user;

        $args['order_manager_id'] = $this->database->isInstalled() ? Modules::get('Stato dei serivizi')['id'] : null;
        $args['is_mobile'] = isMobile();

        // Versione
        $args['version'] = \Update::getVersion();
        $args['revision'] = \Update::getRevision();

        // Richiesta AJAX
        $args['handle_ajax'] = $request->isXhr() && filter('ajax');

        // Calendario
        // Periodo di visualizzazione
        if (!empty($_GET['period_start'])) {
            $_SESSION['period_start'] = $_GET['period_start'];
            $_SESSION['period_end'] = $_GET['period_end'];
        }
        // Dal 01-01-yyy al 31-12-yyyy
        elseif (!isset($_SESSION['period_start'])) {
            $_SESSION['period_start'] = date('Y').'-01-01';
            $_SESSION['period_end'] = date('Y').'-12-31';
        }

        $args['calendar'] = [
            'start' => $_SESSION['period_start'],
            'end' => $_SESSION['period_end'],
            'is_current' => ($_SESSION['period_start'] != date('Y').'-01-01' || $_SESSION['period_end'] != date('Y').'-12-31'),
        ];

        // Argomenti di ricerca dalla sessione
        $search = [];
        $array = $_SESSION['module_'.$id_module];
        if (!empty($array)) {
            foreach ($array as $field => $value) {
                if (!empty($value) && starts_with($field, 'search_')) {
                    $field_name = str_replace('search_', '', $field);

                    $search[$field_name] = $value;
                }
            }
        }
        $args['search'] = $search;

        // Menu principale
        $args['main_menu'] = Modules::getMainMenu();

        // Impostazione degli argomenti
        $request = $this->setArgs($request, $args);

        return $next($request, $response);
    }
}
