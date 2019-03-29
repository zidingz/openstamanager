<?php

namespace Controllers;

use HTMLBuilder\HTMLBuilder;
use Slim\Exception\NotFoundException;

class ModuleController extends Controller
{
    public function module($request, $response, $args)
    {
        // Elenco dei plugin
        $plugins = $args['module']->plugins()->where('position', 'tab_main')->get()->sortBy('order');
        $args['plugins'] = $plugins;

        $result = $this->oldController($args);
        $args = array_merge($args, $result);
        $args['custom_content'] = $args['content'];

        $response = $this->twig->render($response, 'old/controller.twig', $args);

        return $response;
    }

    public function edit($request, $response, $args)
    {
        // Rimozione record precedenti sulla visita della pagina
        $this->database->delete('zz_semaphores', [
            'id_utente' => $args['user']['id'],
            'posizione' => $args['module_id'].', '.$args['record_id'],
        ]);

        // Creazione nuova visita
        $this->database->insert('zz_semaphores', [
            'id_utente' => $args['user']['id'],
            'posizione' => $args['module_id'].', '.$args['record_id'],
        ]);

        // Elenco delle operazioni
        $operations = $this->database->fetchArray('SELECT `zz_operations`.*, `zz_users`.`username` FROM `zz_operations`
            JOIN `zz_users` ON `zz_operations`.`id_utente` = `zz_users`.`id`
            WHERE id_module = '.prepare($args['module_id']).' AND id_record = '.prepare($args['record_id']).'
        ORDER BY `created_at` ASC LIMIT 200');

        foreach ($operations as $key => $operation) {
            $description = $operation['op'];
            $icon = 'pencil-square-o';
            $color = null;
            $tags = null;

            switch ($operation['op']) {
                case 'add':
                $description = tr('Creazione');
                $icon = 'plus';
                $color = 'success';
                break;

                case 'update':
                $description = tr('Modifica');
                $icon = 'pencil';
                $color = 'info';
                break;

                case 'delete':
                $description = tr('Eliminazione');
                $icon = 'times';
                $color = 'danger';
                break;

                default:
                $tags = ' class="timeline-inverted"';
                break;
            }

            $operation['tags'] = $tags;
            $operation['color'] = $color;
            $operation['icon'] = $icon;
            $operation['description'] = $description;

            $operations[$key] = $operation;
        }

        $args['operations'] = $operations;
        $args['include_operations'] = true;

        // Elenco dei plugin
        $plugins = $args['module']->plugins()->where('position', 'tab')->get()->sortBy('order');
        $args['plugins'] = $plugins;

        $result = $this->oldEditor($args);
        $args = array_merge($args, $result);

        $response = $this->twig->render($response, 'old/editor.twig', $args);

        return $response;
    }

    public function editRecord($request, $response, $args)
    {
        $record_id = $this->oldActions($args);

        $route = $this->router->pathFor('module-record', [
            'module_id' => $args['module_id'],
            'record_id' => $record_id,
        ]);

        $response = $response->withRedirect($route);

        return $response;
    }

    public function add($request, $response, $args)
    {
        $args['query_params'] = $request->getQueryParams();
        $response = $this->view->render($response, 'resources\views\add.php', $args);

        return $response;
    }

    public function addRecord($request, $response, $args)
    {
        $record_id = $this->oldActions($args);

        $route = $this->router->pathFor('module-record', [
            'module_id' => $args['module_id'],
            'record_id' => $record_id,
        ]);

        $response = $response->withRedirect($route);

        return $response;
    }

    public function recordAction($request, $response, $args)
    {
        $class = '\\'.$args['structure']->namespace.'\Record';

        if (!class_exists($class)) {
            throw new NotFoundException($request, $response);
        }

        $controller = new $class($this->container);

        return $controller->manage($args['action_name'], $request, $response, $args);
    }

    public function moduleAction($request, $response, $args)
    {
        $class = '\\'.$args['structure']->namespace.'\Module';

        if (!class_exists($class)) {
            throw new NotFoundException($request, $response);
        }

        $controller = new $class($this->container);

        return $controller->manage($args['action_name'], $request, $response, $args);
    }

    protected function oldEditor($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = $structure->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $args['record'] = $record;

        // Registrazione del record
        HTMLBuilder::setRecord($record);

        $content = $structure->filepath('edit.php');
        if (!empty($content)) {
            ob_start();
            include $content;
            $content = ob_get_clean();
        }

        $buttons = $structure->filepath('buttons.php');
        if (!empty($buttons)) {
            ob_start();
            include $buttons;
            $buttons = ob_get_clean();
        }

        $module_bulk = $structure->filepath('bulk.php');
        $module_bulk = empty($module_bulk) ? [] : include $module_bulk;
        $module_bulk = empty($module_bulk) ? [] : $module_bulk;

        return [
            'buttons' => $buttons,
            'editor_content' => $content,
            'bulk' => $module_bulk,
            'plugins_content' => $this->oldPlugins($args),
        ];
    }

    protected function oldActions($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = $structure->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $args['record'] = $record;

        // Registrazione del record
        $actions = $structure->filepath('actions.php');
        if (!empty($actions)) {
            include $actions;
        }

        return $id_record;
    }

    protected function oldPlugins($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Plugins
        $plugins_content = [];

        $module_record = $record;
        foreach ($args['plugins'] as $plugin) {
            $record = $module_record;
            $id_plugin = $plugin['id'];

            $bulk = null;
            $content = null;

            // Inclusione di eventuale plugin personalizzato
            if (!empty($plugin['script']) || $plugin->option == 'custom') {
                ob_start();
                include $plugin->getEditFile();
                $content = ob_get_clean();
            } else {
                $bulk = $args['structure']->filepath('bulk.php');
                $bulk = empty($bulk) ? [] : include $bulk;
                $bulk = empty($bulk) ? [] : $bulk;
            }

            $plugins_content[$id_plugin] = [
                'content' => $content,
                'bulk' => $bulk,
            ];
        }

        return $plugins_content;
    }

    protected function oldController($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        if ($args['structure']->option == 'custom') {
            // Lettura risultato query del modulo
            $init = $args['structure']->filepath('init.php');
            if (!empty($init)) {
                include $init;
            }

            $args['record'] = $record;

            $content = $args['structure']->filepath('edit.php');
            if (!empty($content)) {
                ob_start();
                include $content;
                $content = ob_get_clean();
            }
        }

        return [
            'content' => $content,
            'plugins_content' => $this->oldPlugins($args),
        ];
    }
}
