<?php

namespace Widgets;

use Modules\Module;
use Util\Query;

/**
 * Tipologia di widget indirizzato alla visualizzazione di una statistica informativa per l'utente finale.
 * Presenta un titolo e una valore personalizzato; al click non prevede particolari operazioni.
 *
 * @since 2.5
 */
abstract class StatsWidget extends Manager
{
    abstract public function getQuery(): string;

    protected function getContent(): string
    {
        $widget = $this->model;

        // Individuazione della query relativa
        $query = $this->getQuery();

        $module = Module::get($widget['id_module']);
        $additionals = $module->getAdditionalsQuery();
        if (!empty($additionals)) {
            $query = str_replace('1=1', '1=1 '.$additionals, $query);
        }

        $query = Query::replacePlaceholder($query);

        // Individuazione del risultato della query
        $database = database();
        $value = null;
        if (!empty($query)) {
            $value = $database->fetchArray($query)[0]['dato'];
            if (!preg_match('/\\d/', $value)) {
                $value = '-';
            }
        }

        return $value;
    }
}
