<?php

namespace Modules\Retro;

use App;
use Controllers\Controller;
use HTMLBuilder\HTMLBuilder;
use Modules\Traits\DefaultTrait;

abstract class RetroController extends Controller
{
    use DefaultTrait;

    public static function filepath($module, $file)
    {
        return App::filepath('modules/'.$module->directory.'|custom|', $file);
    }

    protected function controller($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        if ($args['structure']->option == 'custom') {
            // Lettura risultato query del modulo
            $init = $args['module']->filepath('init.php');
            if (!empty($init)) {
                include $init;
            }

            $args['record'] = $record;

            $content = $args['module']->filepath('edit.php');
            if (!empty($content)) {
                ob_start();
                include $content;
                $content = ob_get_clean();
            }
        }

        $args = array_merge($args, [
            'content' => $content,
            'plugins_content' => $this->plugins($args),
        ]);

        $args['custom_content'] = $content;

        return $args;
    }

    protected function editor($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = $args['module']->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $args['record'] = $record;

        // Registrazione del record
        HTMLBuilder::setRecord($record);

        $content = $args['module']->filepath('edit.php');
        if (!empty($content)) {
            ob_start();
            include $content;
            $content = ob_get_clean();
        }

        $buttons = $args['module']->filepath('buttons.php');
        if (!empty($buttons)) {
            ob_start();
            include $buttons;
            $buttons = ob_get_clean();
        }

        $module_bulk = $args['module']->filepath('bulk.php');
        $module_bulk = empty($module_bulk) ? [] : include $module_bulk;
        $module_bulk = empty($module_bulk) ? [] : $module_bulk;

        $args = array_merge($args, [
            'buttons' => $buttons,
            'content' => $content,
            'bulk' => $module_bulk,
            'plugins_content' => $this->plugins($args),
        ]);

        return $args;
    }

    protected function actions($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = $args['module']->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $args['record'] = $record;

        // Registrazione del record
        $actions = $args['module']->filepath('actions.php');
        if (!empty($actions)) {
            include $actions;
        }

        return $id_record;
    }

    protected function add($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = $args['module']->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $content = $args['structure']->getAddFile();
        if (!empty($content)) {
            ob_start();
            include $content;
            $content = ob_get_clean();
        }

        $args = array_merge($args, [
            'content' => $content,
        ]);

        return $args;
    }

    /*
        protected function plugins($args)
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
                    $bulk = $args['module']->filepath('bulk.php');
                    $bulk = empty($bulk) ? [] : include $bulk;
                    $bulk = empty($bulk) ? [] : $bulk;
                }

                $plugins_content[$id_plugin] = [
                    'content' => $content,
                    'bulk' => $bulk,
                ];
            }

            return $plugins_content;
        }*/
}
