<?php

use Modules\Module;

/**
 * Classe per la gestione delle informazioni relative ai moduli installati.
 *
 * @since 2.3
 *
 * @deprecated
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
