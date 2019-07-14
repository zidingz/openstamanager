<?php

namespace Managers;

use Controllers\Controller;
use HTMLBuilder\HTMLBuilder;
use Slim\Exception\NotFoundException;

class RetroController extends Controller
{
    public function controller($request, $response, $args)
    {
        $result = $this->oldController($args);
        $args = array_merge($args, $result);
        $args['custom_content'] = $args['content'];

        return $this->twig->render($response, 'old/controller.twig', $args);
    }

    public function editor($request, $response, $args)
    {
        $result = $this->oldEditor($args);
        $args = array_merge($args, $result);

        return $this->twig->render($response, 'old/editor.twig', $args);
    }

    public function actions($request, $response, $args)
    {
        $record_id = $this->oldActions($args);

        return $record_id;
    }

    public function add($request, $response, $args)
    {
        $result = $this->oldAdd($args);
        $args = array_merge($args, $result);

        return $this->twig->render($response, 'old/add.twig', $args);
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

    protected function oldAdd($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = $args['structure']->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $content = $args['structure']->getAddFile();
        if (!empty($content)) {
            ob_start();
            include $content;
            $content = ob_get_clean();
        }

        return [
            'content' => $content,
        ];
    }
}
