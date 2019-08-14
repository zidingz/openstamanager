<?php

namespace Modules\Aggiornamenti;

use Modules\HookManager;
use Modules;

class UpdateHook extends HookManager
{
    public function manage()
    {
        $result = Aggiornamento::isAvailable();

        return $result;
    }

    public function response($update)
    {
        $module = Modules::get('Aggiornamenti');
        $link = ROOTDIR.'/controller.php?id_module='.$module->id;

        $message = tr("E' disponibile la versione _VERSION_ del gestionale", [
            '_VERSION_' => $update,
        ]);

        return [
            'icon' => 'fa fa-download text-info',
            'link' => $link,
            'message' => $message,
            'notify' => !empty($update),
        ];
    }
}
