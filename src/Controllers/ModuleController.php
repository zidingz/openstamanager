<?php

namespace Controllers;

use Managers\RetroController;
use Slim\Exception\NotFoundException;

class ModuleController extends Controller
{
    public function module($request, $response, $args)
    {
        // Elenco dei plugin
        $plugins = $args['module']->plugins()->where('position', 'tab_main')->get()->sortBy('order');
        $args['plugins'] = $plugins;

        if ($this->isRetro($args)) {
            $controller = new RetroController($this->container);

            $response = $controller->controller($request, $response, $args);
        } else {
            $class = $this->getModuleManager($request, $response, $args);
            $controller = new $class($this->container);

            $response = $controller->page($request, $response, $args);
        }

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

        if ($this->isRetro($args)) {
            $controller = new RetroController($this->container);

            $response = $controller->editor($request, $response, $args);
        } else {
            $class = $this->getRecordManager($request, $response, $args);
            $controller = new $class($this->container);

            $response = $controller->page($request, $response, $args);
        }

        return $response;
    }

    public function editRecord($request, $response, $args)
    {
        if ($this->isRetro($args)) {
            $controller = new RetroController($this->container);

            $record_id = $controller->actions($request, $response, $args);
        } else {
            $class = $this->getRecordManager($request, $response, $args);
            $controller = new $class($this->container);

            $record_id = $controller->update($request, $response, $args);
        }

        if (!empty($record_id)) {
            $route = $this->router->pathFor('module-record', [
                'module_id' => $args['module_id'],
                'record_id' => $record_id,
            ]);
        } else {
            $route = $this->router->pathFor('module', [
                'module_id' => $args['module_id'],
            ]);
        }

        $response = $response->withRedirect($route);

        return $response;
    }

    public function add($request, $response, $args)
    {
        $args['query_params'] = [];
        $query = $request->getQueryParams();
        foreach ($query as $key => $value) {
            $args['query_params'][$key] = get($key);
        }

        if ($this->isRetro($args)) {
            $controller = new RetroController($this->container);

            $response = $controller->add($request, $response, $args);
        } else {
            $class = $this->getModuleManager($request, $response, $args);
            $controller = new $class($this->container);

            $response = $controller->add($request, $response, $args);
        }

        return $response;
    }

    public function addRecord($request, $response, $args)
    {
        if ($this->isRetro($args)) {
            $controller = new RetroController($this->container);

            $record_id = $controller->actions($request, $response, $args);
        } else {
            $class = $this->getModuleManager($request, $response, $args);
            $controller = new $class($this->container);

            $record_id = $controller->create($request, $response, $args);
        }

        if (!empty($record_id)) {
            $route = $this->router->pathFor('module-record', [
                'module_id' => $args['module_id'],
                'record_id' => $record_id,
            ]);
        } else {
            $route = $this->router->pathFor('module', [
                'module_id' => $args['module_id'],
            ]);
        }

        $response = $response->withRedirect($route);

        return $response;
    }

    public function recordAction($request, $response, $args)
    {
        $class = $this->getRecordActionsManager($request, $response, $args);
        $controller = new $class($this->container);

        return $controller->manage($args['action'], $request, $response, $args);
    }

    public function moduleAction($request, $response, $args)
    {
        $class = $this->getModuleActionsManager($request, $response, $args);
        $controller = new $class($this->container);

        return $controller->manage($args['action'], $request, $response, $args);
    }

    protected function getRecordManager($request, $response, $args)
    {
        $class = $args['structure']->namespace.'\Record';

        if (!class_exists($class)) {
            throw new NotFoundException($request, $response);
        }

        return $class;
    }

    protected function getModuleManager($request, $response, $args)
    {
        $class = $args['structure']->namespace.'\Module';

        if (!class_exists($class)) {
            throw new NotFoundException($request, $response);
        }

        return $class;
    }

    protected function getModuleActionsManager($request, $response, $args)
    {
        $class = $args['structure']->namespace.'\ModuleActions';

        if (!class_exists($class)) {
            return $this->getModuleManager($request, $response, $args);
        }

        return $class;
    }

    protected function getRecordActionsManager($request, $response, $args)
    {
        $class = $args['structure']->namespace.'\RecordActions';

        if (!class_exists($class)) {
            return $this->getRecordManager($request, $response, $args);
        }

        return $class;
    }

    protected function isRetro($args)
    {
        return empty($args['structure']->namespace);
    }
}
