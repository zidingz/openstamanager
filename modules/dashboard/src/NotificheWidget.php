<?php

namespace Modules\Dashboard;

use Carbon\Carbon;
use Modules\Module;
use Widgets\Retro\ModalWidget;

/**
 * Classe dedicata alla gestione del widget relativo alle Note interne in scadenza.
 *
 * @since 2.5
 */
class NotificheWidget extends ModalWidget
{
    public function getNotes()
    {
        $notes = collect();

        $moduli = Module::getAll()->where('permission', '<>', '-');
        foreach ($moduli as $modulo) {
            $note = $modulo->notes()->where('notification_date', '>=', date('Y-m-d'))->get();
            $notes = $notes->merge($note);
        }

        return $notes;
    }

    public function getModal(): string
    {
        $notes = $this->getNotes();

        if ($notes->isEmpty()) {
            return '
<p>'.tr('Non ci sono note da notificare').'.</p>';
        }

        $html = '';
        $moduli = $notes->groupBy('id_module')->sortBy('notification_date');
        foreach ($moduli as $module_id => $note) {
            $modulo = Module::get($module_id);

            $html .= '
<h4>'.$modulo->title.'</h4>
<table class="table table-hover">
    <tr>
        <th width="5%" >'.tr('Record').'</th>
        <th>'.tr('Contenuto').'</th>
        <th width="20%" class="text-center">'.tr('Data di notifica').'</th>
        <th class="text-center">#</th>
    </tr>';

            foreach ($note as $nota) {
                $html .= '
    <tr>
        <td>'.$nota->id_record.'</td>

        <td>
            <span class="pull-right"></span>

            '.$nota->content.'

            <small>'.$nota->user->nome_completo.'</small>
        </td>

        <td class="text-center">
            '.dateFormat($nota->notification_date).' ('.Carbon::parse($nota->notification_date)->diffForHumans().')
        </td>

        <td class="text-center">
            '.Modules::link($module_id, $nota->id_record, '', null, 'class="btn btn-primary btn-xs"', true, 'tab_note').'
        </td>
    </tr>';
            }

            $html .= '
</table>';
        }

        return $html;
    }

    protected function getTitle(): string
    {
        return tr('Notifiche interne');
    }

    protected function getContent(): string
    {
        $notes = $this->getNotes();

        return $notes->count();
    }
}
