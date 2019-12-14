<?php

namespace Modules\Retro;

use App;
use Controllers\Controller;
use HTMLBuilder\HTMLBuilder;
use Modules\Traits\DefaultTrait;

abstract class Parser extends Controller
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

        if ($args['module']->option == 'custom') {
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
        ]);

        $args['include_operations'] = true;
        $args['operations'] = $this->getOperations($args['module'], $args['id_record']);

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

        $content = $args['module']->getAddFile();
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
}
