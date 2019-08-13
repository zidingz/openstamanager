<?php

namespace Controllers;

use Controllers\Retro\ActionManager;
use Slim\Exception\NotFoundException;

class ModuleController extends Controller
{
    /**
     * Gestione della pagina principale del modulo.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function module($request, $response, $args)
    {
        // Elenco dei plugin
        $args['plugins'] = $args['module']->children()->where('type', 'plugin_module')->get()->sortBy('order');

        $controller = $this->getModuleManager($request, $response, $args);

        $args['reference_record'] = $controller->getReferenceRecord($args);

        $response = $controller->page($request, $response, $args);

        return $response;
    }

    /**
     * Gestione della pagine del record.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
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
        $args['plugins'] = $args['module']->children()->where('type', 'plugin_record')->get()->sortBy('order');

        $controller = $this->getRecordManager($request, $response, $args);

        $args['reference_record'] = $controller->getReferenceRecord($args);

        $response = $controller->page($request, $response, $args);

        return $response;
    }

    /**
     * Gestione del salvataggio delle informazioni del record.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function editRecord($request, $response, $args)
    {
        $controller = $this->getRecordManager($request, $response, $args);

        $record_id = $controller->update($request, $response, $args);

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

    /**
     * Gestione della pagina di creazione nuovo record.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function add($request, $response, $args)
    {
        $args['query_params'] = [];
        $query = $request->getQueryParams();
        foreach ($query as $key => $value) {
            $args['query_params'][$key] = get($key);
        }

        $controller = $this->getModuleManager($request, $response, $args);

        $args['reference_record'] = $controller->getReferenceRecord($args);

        $response = $controller->add($request, $response, $args);

        return $response;
    }

    /**
     * Gestione del salvataggio delle informazioni del nuovo record.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function addRecord($request, $response, $args)
    {
        $controller = $this->getModuleManager($request, $response, $args);

        $record_id = $controller->create($request, $response, $args);

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

    /**
     * Azioni personalizzate sul record.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function recordAction($request, $response, $args)
    {
        $controller = $this->getRecordActionsManager($request, $response, $args);

        return $this->action($request, $response, $args, $controller);
    }

    /**
     * Azioni personalizzate sul modulo.
     *
     * @param $request
     * @param $response
     * @param $args
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function moduleAction($request, $response, $args)
    {
        $class = self::getModuleActionsManager($request, $response, $args);
        $controller = new $class($this->container);

        return $this->action($request, $response, $args, $controller);
    }

    public function getRecordManager($request, $response, $args)
    {
        return $this->getController($request, $response, $args['structure'], 'Record');
    }

    public function getModuleManager($request, $response, $args)
    {
        return $this->getController($request, $response, $args['structure'], 'Module');
    }

    public function getModuleActionsManager($request, $response, $args)
    {
        return $this->getController($request, $response, $args['structure'], 'ModuleActions');
    }

    public function getRecordActionsManager($request, $response, $args)
    {
        return $this->getController($request, $response, $args['structure'], 'RecordActions');
    }

    public function getController($request, $response, $module, $name)
    {
        $class = self::getControllerClass($module, $name);

        if (empty($class)) {
            throw new NotFoundException($request, $response);
        }

        $controller = new $class($this->container);

        return $controller;
    }

    public static function getControllerClass($module, $name)
    {
        $class = $module->namespace.'\\'.$name;

        if (!class_exists($class)) {
            return null;
        }

        return $class;
    }

    protected function action($request, $response, $args, $controller)
    {
        $action = str_replace(['-', '_'], [' ', ' '], $args['action']);
        $action = lcfirst(ucwords($action));
        $action = str_replace(' ', '', $action);

        if (!method_exists($controller, $action) && !$controller instanceof ActionManager) {
            throw new NotFoundException($request, $response);
        }

        $response = $controller->{$action}($request, $response, $args);

        return $response;
    }
}
