<?php

use Modules\Aggiornamenti\Aggiornamento;
use Modules\Aggiornamenti\DowngradeException;

use Modules\Aggiornamenti\UpdateHook;

$id = post('id');

switch (filter('op')) {
    case 'check':
        $result = Aggiornamento::isAvailable();

        if ($result === false) {
            echo 'none';
        } else {
            echo $result;
        }

        break;

    case 'download':
        $update = Aggiornamento::download();

        break;

    case 'upload':
        if (!setting('Attiva aggiornamenti')) {
            die(tr('Accesso negato'));
        }

        if (!extension_loaded('zip')) {
            flash()->error(tr('Estensione zip non supportata!').'<br>'.tr('Verifica e attivala sul tuo file _FILE_', [
                '_FILE_' => '<b>php.ini</b>',
            ]));

            return;
        }

        try {
            $update = Aggiornamento::make($_FILES['blob']['tmp_name']);
        } catch (DowngradeException $e) {
            flash()->error(tr('Il pacchetto contiene una versione precedente del gestionale'));
        } catch (InvalidArgumentException $e) {
            flash()->error(tr('Il pacchetto contiene solo componenti già installate e aggiornate'));
        }

        break;
}
