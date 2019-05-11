<?php

namespace Plugins\ImportFE;

use Managers\HookManager;
use Modules;

class InvoiceHook extends HookManager
{
    public function manage()
    {
        $list = Interaction::listToImport();

        return $list;
    }

    public function response($results)
    {
        $count = count($results);

        $module = Modules::get('Fatture di acquisto');
        $plugin = $module->plugins->first(function ($value, $key) {
            return $value->name == 'Fatturazione Elettronica';
        });

        $link = pathFor('module', [
            'module_id' => $module->id,
        ]).'#tab_'.$plugin->id;

        return [
            'icon' => 'fa fa-file-text-o',
            'link' => $link,
            'message' => tr('Ci sono _NUM_ fatture remote da importare', [
                '_NUM_' => $count,
            ]),
            'notify' => !empty($count),
        ];
    }
}
