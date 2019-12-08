<?php

use Models\Module;

/**
 * Classe per la gestione delle informazioni relative ai moduli installati.
 *
 * @since 2.3
 */
class Modules
{
    /** @var array Elenco delle condizioni aggiuntive disponibili */
    protected static $additionals = [];
    /** @var array Elenco dei segmenti disponibili */
    protected static $segments = [];

    /** @var array Elenco gerarchico dei moduli */
    protected static $hierarchy;

    /**
     * Restituisce tutte le informazioni di tutti i moduli installati.
     *
     * @return array
     */
    public static function getModules()
    {
        $results = Module::getAll();

        // Caricamento dei plugin
        if (!$results->first()->relationLoaded('plugins')) {
            $results->load('plugins');
        }

        return $results;
    }

    /**
     * Restituisce l'elenco dei moduli con permessi di accesso accordati.
     *
     * @return array
     */
    public static function getAvailableModules()
    {
        // Individuazione dei moduli con permesso di accesso
        $modules = self::getModules();

        foreach ($modules as $key => $module) {
            if ($module->permission == '-') {
                unset($modules[$key]);
            }
        }

        return $modules;
    }

    /**
     * Restituisce le informazioni relative a un singolo modulo specificato.
     *
     * @param string|int $module
     *
     * @return Module
     */
    public static function get($module)
    {
        self::getModules();

        return Module::get($module);
    }

    /**
     * Restituisce il modulo attualmente in utilizzo.
     *
     * @return Module
     */
    public static function getCurrent()
    {
        return Module::getCurrent();
    }

    /**
     * Imposta il modulo attualmente in utilizzo.
     *
     * @param int $id
     */
    public static function setCurrent($id)
    {
        Module::setCurrent($id);
    }

    /**
     * Restituisce i permessi accordati all'utente in relazione al modulo specificato.
     *
     * @param string|int $module
     *
     * @return string
     */
    public static function getPermission($module)
    {
        return self::get($module)->permission;
    }

    /**
     * Restituisce i filtri aggiuntivi dell'utente in relazione al modulo specificato.
     *
     * @param int $id
     *
     * @return string
     */
    public static function getAdditionals($module, $include_segments = true)
    {
        $module = self::get($module);
        $user = Auth::user();

        if (!isset(self::$additionals[$module['id']])) {
            $database = database();

            $additionals['WHR'] = [];
            $additionals['HVN'] = [];

            $results = $database->fetchArray('SELECT * FROM `zz_group_module` WHERE `idgruppo` = (SELECT `idgruppo` FROM `zz_users` WHERE `id` = '.prepare($user['id']).') AND `enabled` = 1 AND `idmodule` = '.prepare($module['id']));
            foreach ($results as $result) {
                if (!empty($result['clause'])) {
                    $result['clause'] = Util\Query::replacePlaceholder($result['clause']);

                    $additionals[$result['position']][] = $result['clause'];
                }
            }

            // Aggiunta dei segmenti
            if ($include_segments) {
                $segments = self::getSegments($module['id']);
                $id_segment = $_SESSION['module_'.$module['id']]['id_segment'];
                foreach ($segments as $result) {
                    if (!empty($result['clause']) && $result['id'] == $id_segment) {
                        $result['clause'] = Util\Query::replacePlaceholder($result['clause']);

                        $additionals[$result['position']][] = $result['clause'];
                    }
                }
            }

            self::$additionals[$module['id']] = $additionals;
        }

        return (array) self::$additionals[$module['id']];
    }

    /**
     * Restituisce i filtri aggiuntivi dell'utente in relazione al modulo specificato.
     *
     * @param int $module
     *
     * @return array
     */
    public static function getSegments($module)
    {
        if (Update::isUpdateAvailable()) {
            return [];
        }

        $module = self::get($module)['id'];

        if (!isset(self::$segments[$module])) {
            $database = database();

            self::$segments[$module] = $database->fetchArray('SELECT * FROM `zz_segments` WHERE `id_module` = '.prepare($module).' ORDER BY `predefined` DESC, `id` ASC');
        }

        return (array) self::$segments[$module];
    }

    /**
     * Restituisce le condizioni SQL aggiuntive del modulo.
     *
     * @param string $name
     *
     * @return array
     */
    public static function getAdditionalsQuery($module, $type = null, $include_segments = true)
    {
        $array = self::getAdditionals($module, $include_segments);
        if (!empty($type) && isset($array[$type])) {
            $result = (array) $array[$type];
        } else {
            $result = array_merge((array) $array['WHR'], (array) $array['HVN']);
        }

        $result = implode(' AND ', $result);

        $result = empty($result) ? $result : ' AND '.$result;

        return $result;
    }

    public static function replaceAdditionals($id_module, $query)
    {
        $result = $query;

        // Aggiunta delle condizione WHERE
        $result = str_replace('1=1', '1=1'.self::getAdditionalsQuery($id_module, 'WHR'), $result);

        // Aggiunta delle condizione HAVING
        $result = str_replace('2=2', '2=2'.self::getAdditionalsQuery($id_module, 'HVN'), $result);

        return $result;
    }

    /**
     * Restituisce tutte le informazioni dei moduli installati in una scala gerarchica fino alla profondità indicata.
     *
     *
     * @param int $depth
     *
     * @return array
     */
    public static function getHierarchy()
    {
        if (!isset(self::$hierarchy)) {
            self::$hierarchy = Module::getHierarchy()->toArray();
        }

        return self::$hierarchy;
    }

    /**
     * Costruisce un link HTML per il modulo e il record indicati.
     *
     * @param string|int $modulo
     * @param int        $id_record
     * @param string     $testo
     * @param bool       $alternativo
     * @param string     $extra
     * @param bool       $blank
     * @param string     $anchor
     *
     * @return string
     */
    public static function link($modulo, $id_record = null, $testo = null, $alternativo = true, $extra = null, $blank = true, $anchor = null)
    {
        $testo = isset($testo) ? nl2br($testo) : tr('Visualizza scheda');
        $alternativo = is_bool($alternativo) && $alternativo ? $testo : $alternativo;

        // Aggiunta automatica dell'icona di riferimento
        if (!str_contains($testo, '<i ')) {
            $testo = $testo.' <i class="fa fa-external-link"></i>';
        }

        $module = self::get($modulo);

        $extra .= !empty($blank) ? ' target="_blank"' : '';

        if (!empty($module) && in_array($module->permission, ['r', 'rw'])) {
            $link = !empty($id_record) ? $module->url('record', [
                'record_id' => $id_record,
            ]) : $module->url('module');

            return '<a href="'.$link.'#'.$anchor.'" '.$extra.'>'.$testo.'</a>';
        } else {
            return $alternativo;
        }
    }

    /**
     * Individua il percorso per il file.
     *
     * @param string|int $element
     * @param string     $file
     *
     * @return string|null
     */
    public static function filepath($element, $file)
    {
        $element = self::get($element);

        return $element ? $element->filepath($file) : null;
    }
}
