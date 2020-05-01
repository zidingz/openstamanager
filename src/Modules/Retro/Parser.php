<?php

namespace Modules\Retro;

use App;
use Controllers\Controller;
use HTMLBuilder\HTMLBuilder;
use Modules\Module;
use Modules\Traits\DefaultTrait;

abstract class Parser extends Controller
{
    use DefaultTrait;

    protected function controller($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        if ($args['module']->option == 'custom') {
            // Lettura risultato query del modulo
            $init = self::filepath($args['module'], 'init.php');
            if (!empty($init)) {
                include $init;
            }

            $args['record'] = $record;

            $content = self::filepath($args['module'], 'edit.php');
            if (!empty($content)) {
                ob_start();
                include $content;
                $content = ob_get_clean();
            }
        } elseif (!in_array($args['module']->option, ['custom', 'menu'])) {
            // TODO: fix per la visualizzazione dei record collegati al record genitore del plugin, visualizzazione titolo con pulsante di aggiunta
            $content = $this->twig->fetch('content.twig', $args);
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
        $init = self::filepath($args['module'], 'init.php');
        if (!empty($init)) {
            include $init;
        }

        $args['record'] = $record;

        // Registrazione del record
        HTMLBuilder::setRecord($record);

        $content = self::filepath($args['module'], 'edit.php');
        if (!empty($content)) {
            ob_start();
            include $content;
            $content = ob_get_clean();
        }

        $buttons = self::filepath($args['module'], 'buttons.php');
        if (!empty($buttons)) {
            ob_start();
            include $buttons;
            $buttons = ob_get_clean();
        }

        $module_bulk = self::filepath($args['module'], 'bulk.php');
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
        $init = self::filepath($args['module'], 'init.php');
        if (!empty($init)) {
            include $init;
        }

        $args['record'] = $record;

        // Registrazione del record
        $actions = self::filepath($args['module'], 'actions.php');
        if (!empty($actions)) {
            include $actions;
        }

        return $id_record;
    }

    protected function create($args)
    {
        extract($args);

        $dbo = $database = $this->database;

        // Lettura risultato query del modulo
        $init = self::filepath($args['module'], 'init.php');
        if (!empty($init)) {
            include $init;
        }

        $content = $this->getAddFile($args['module']);
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

    /**
     * Restituisce il percorso per il file di creazione dei record.
     *
     * @return string
     */
    public function getAddFile(Module $module):?string
    {
        $php = self::filepath($module, 'add.php');
        $html = self::filepath($module, 'add.html');

        return !empty($php) ? $php : $html;
    }

    /**
     * Restituisce il percorso per il file di modifica dei record.
     *
     * @return string
     */
    public function getEditFile(Module $module):?string
    {
        $php = self::filepath($module, 'edit.php');
        $html = self::filepath($module, 'edit.html');

        return !empty($php) ? $php : $html;
    }

    /**
     * Restituisce il percorso completo per il file indicato della struttura.
     *
     * @return string|null
     */
    public static function filepath(Module $module, string $file):?string
    {
        return App::filepath('modules/'.$module->directory.'|custom|', $file);
    }

    /**
     * Restituisce l'URL completa per il file indicato della struttura.
     *
     * @return string|null
     */
    public static function fileurl(Module $module, string $file): ?string
    {
        $filepath = self::filepath($module, $file);

        $result = str_replace(DOCROOT, ROOTDIR, $filepath);
        $result = str_replace('\\', '/', $result);

        return $result;
    }
}
