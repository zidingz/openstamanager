<?php

namespace Modules\Aggiornamenti;

use Hooks\CachedManager;
use Modules;

class UpdateHook extends CachedManager
{
    public function data()
    {
        $result = Aggiornamento::isAvailable();

        return $result;
    }

    public function response()
    {
        $update = self::getCache()['results'];

        $module = \Modules\Module::get('Aggiornamenti');
        $link = ROOTDIR.'/controller.php?id_module='.$module->id;

        $message = tr("E' disponibile la versione _VERSION_ del gestionale", [
            '_VERSION_' => $update,
        ]);

        return [
            'icon' => 'fa fa-download text-info',
            'link' => $link,
            'message' => $message,
            'show' => !empty($update),
        ];
    }
}
