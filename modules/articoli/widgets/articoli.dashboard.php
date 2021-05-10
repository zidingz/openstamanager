<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
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

include_once __DIR__.'/../../../core.php';

$rs = $dbo->fetchArray('SELECT id, descrizione, qta, threshold_qta, codice, um AS unitamisura FROM mg_articoli WHERE qta < threshold_qta AND attivo = 1 ORDER BY qta ASC');

if (!empty($rs)) {
    echo '
<table class="table table-hover table-striped">
    <tr>
        <th width="80%">'.tr('Articolo').'</th>
        <th width="20%">'.tr('Q.tà').'</th>
    </tr>';

    foreach ($rs as $r) {
        echo '
    <tr>
        <td>
            '.Modules::link('Articoli', $r['id'], $r['descrizione']).'
            <br><small>'.$r['codice'].'</small>
        </td>
        <td>
            '.numberFormat($r['qta'], 'qta').' '.$r['unitamisura'].'
        </td>
    </tr>';
    }

    echo '
</table>';
} else {
    echo '<div class=\'alert alert-info\' >'.tr('Non ci sono articoli in esaurimento.')."</div>\n";
}
