<?php

namespace Managers;

use Controllers\Controller;
use Controllers\ModuleController;

abstract class ControllerManager extends Controller
{
    abstract public function getReferenceID($args);

    public function getReferenceRecord($args)
    {
        $module = $args['structure'];
        if ($module->type != 'module_plugin' && $module->type != 'record_plugin') {
            return null;
        }

        $class = ModuleController::getControllerClass($module->parent()->first(), 'Record');
        $id_record = $this->getReferenceID($args);

        if (!empty($id_record) && !empty($class)) {
            $manager = new $class($this->container);
            $data = $manager->data($id_record);

            $result = $data['record'];
        }

        return $result;
    }

    protected function plugins($request, $response, $plugins, $args)
    {
        $reference_record = $args['record'] ?: null;

        dd('asss');
        exit();
        $args['record'] = null;
        $args['reference_record'] = $reference_record;
        $args['reference_id'] = $args['record_id'];

        foreach ($plugins as $plugin) {
            $manager = ModuleController::getControllerClass($plugin, 'Module');
            $args['module_id'] = $plugin['id'];

            $plugin->namespace;
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
    }

    protected function plugin($plugin, $reference_record = null)
    {
    }
}
