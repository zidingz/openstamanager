<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.n.c.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Modules\Retro;

use App;
use Controllers\Controller;
use HTMLBuilder\HTMLBuilder;
use Modules\Module;
use Modules\Traits\DefaultTrait;

/**
 * Parser ausiliario per la struttura dei moduli per versioni <= 2.4.
 *
 * @since 2.5
 */
abstract class Parser extends Controller
{
    use DefaultTrait;

    /**
     * Restituisce il percorso per il file di creazione dei record.
     *
     * @return string
     */
    public function getAddFile(Module $module): ?string
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
    public function getEditFile(Module $module): ?string
    {
        $php = self::filepath($module, 'edit.php');
        $html = self::filepath($module, 'edit.html');

        return !empty($php) ? $php : $html;
    }

    /**
     * Restituisce il percorso completo per il file indicato della struttura.
     */
    public static function filepath(Module $module, string $file): ?string
    {
        return App::filepath('modules/'.$module->directory.'|custom|', $file);
    }

    /**
     * Restituisce l'URL completa per il file indicato della struttura.
     */
    public static function fileurl(Module $module, string $file): ?string
    {
        $filepath = self::filepath($module, $file);

        $result = str_replace(DOCROOT, ROOTDIR, $filepath);
        $result = str_replace('\\', '/', $result);

        return $result;
    }

    /**
     * Simulazione del comportamento previsto dal file controller.php per versioni del gestionale <= 2.4.
     *
     * @param $args
     *
     * @return array
     */
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
            $content = $this->twig->fetch('@resources/modules/content.twig', $args);
        }

        $args = array_merge($args, [
            'content' => $content,
        ]);

        $args['custom_content'] = $content;

        return $args;
    }

    /**
     * Simulazione del comportamento previsto dal file editor.php per versioni del gestionale <= 2.4.
     *
     * @param $args
     *
     * @return array
     */
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

    /**
     * Simulazione del comportamento previsto dal file actions.php per versioni del gestionale <= 2.4.
     *
     * @param $args
     *
     * @return mixed
     */
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

    /**
     * Simulazione del comportamento previsto dal file add.php per versioni del gestionale <= 2.4.
     *
     * @param $args
     *
     * @return array
     */
    protected function add($args)
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

    protected function getReferenceID(array $args)
    {
        return $args['reference_id'];
    }

    protected function getReferenceData(array $args)
    {
        $module = $args['module'];
        if ($module->type == 'module') {
            return [];
        }

        $id_record = $this->getReferenceID($args);
        $data = Module::find($module->parent)->getManager()->getData($id_record);

        return $data;
    }

    /**
     * Completamento delle informazioni per il rendering del modulo.
     *
     * @return array
     */
    protected function prepare(array $args)
    {
        $data = $this->getReferenceData($args);
        $args['reference_record'] = $data['record'];

        $ignore = [
            'module',
            'structure',
            'id_module',
            'module_id',
            'record',
            'id_record',
        ];

        foreach ($ignore as $key) {
            unset($data[$key]);
        }

        $args = array_merge($data, $args);

        return $args;
    }
}
