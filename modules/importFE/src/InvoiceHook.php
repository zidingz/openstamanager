<?php

namespace Plugins\ImportFE;

use Hooks\CachedManager;
use Modules;

class InvoiceHook extends CachedManager
{
    public function data()
    {
        $list = Interaction::getInvoiceList();

        return $list;
    }

    public function response()
    {
        $results = self::getCache()['results'];

        $count = count($results);
        $notify = false;

        $module = Modules::get('Fatture di acquisto');
        $plugins = $module->plugins;

        if (!empty($plugins)) {
            $notify = !empty($count);

            $plugin = $plugins->first(function ($value, $key) {
                return $value->name == 'Importazione FE';
            });

            $link = pathFor('module', [
                'module_id' => $module->id,
            ]).'#tab_'.$plugin->id;
        }

        $message = tr('Ci sono _NUM_ fatture passive da importare', [
            '_NUM_' => $count,
        ]);

        return [
            'icon' => 'fa fa-file-text-o text-yellow',
            'link' => $link,
            'message' => $message,
            'show' => $notify,
        ];
    }
}
