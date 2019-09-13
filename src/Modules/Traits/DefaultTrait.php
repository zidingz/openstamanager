<?php

namespace Modules\Traits;

use Models\Module;

/**
 * Trait dedicato alla gestione delle operazioni di visualizzazione per i template di modifica e aggiunta righe.
 *
 * @since 2.5
 */
trait DefaultTrait
{
    public function getReferenceID(array $args)
    {
        return $args['reference_id'];
    }

    public function getReferenceRecord(array $args)
    {
        $module = $args['structure'];
        if ($module->type == 'module') {
            return null;
        }

        //$class = ModuleController::getControllerClass($module->parent()->first(), 'Record');
        $id_record = $this->getReferenceID($args);
        /*
                if (!empty($id_record) && !empty($class)) {
                    $manager = new $class($this->container);
                    $data = $manager->data($id_record);

                    $result = $data['record'];
                }
        */
        return $result;
    }

    public function getPlugins(string $type = 'module_plugin')
    {
        return $this->module
            ->children()
            ->where('type', $type)
            ->orderBy('order')
            ->get();
    }

    public function registerVisit()
    {
        $user = $this->auth->getUser();
        // Rimozione record precedenti sulla visita della pagina
        $this->database->delete('zz_semaphores', [
            'id_utente' => $user['id'],
            'posizione' => $args['module_id'].', '.$args['record_id'],
        ]);

        // Creazione nuova visita
        $this->database->insert('zz_semaphores', [
            'id_utente' => $user['id'],
            'posizione' => $args['module_id'].', '.$args['record_id'],
        ]);
    }

    public function getOperations()
    {
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

        return $operations;
    }

    public function plugins(array $args)
    {
        $plugins = $args['plugins'];
        $args['plugins'] = [];

        $results = [];
        foreach ($plugins as $plugin) {
            $controller = $plugin->getController($this->container, 'Module', null, $this->record_id);

            if (!empty($controller)) {
                $results[$plugin->id] = $controller->content($args);
            }
        }

        return $results;
    }

    /**
     * Completamento delle informazioni per il rendering del modulo.
     *
     * @param array $args
     *
     * @return array
     */
    protected function prepare(array $args)
    {
        $args['reference_record'] = $this->getReferenceRecord($args);

        return $args;
    }
}
