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

include_once __DIR__.'/../../core.php';

$prima_nota = module('Prima nota');

$id_conto = get('id_conto');

// Calcolo totale conto da elenco movimenti di questo conto
$query = 'SELECT co_movimenti.*,
    SUM(totale) AS totale,
    dir FROM co_movimenti
LEFT OUTER JOIN co_documenti ON co_movimenti.iddocumento = co_documenti.id
LEFT OUTER JOIN co_tipidocumento ON co_documenti.idtipodocumento = co_tipidocumento.id
WHERE co_movimenti.idconto='.prepare($id_conto).' AND
    co_movimenti.data >= '.prepare(session('period_start')).' AND
    co_movimenti.data <= '.prepare(session('period_end')).'
GROUP BY co_movimenti.idmastrino
ORDER BY co_movimenti.data ASC, co_movimenti.descrizione';
$movimenti = $dbo->fetchArray($query);

if (!empty($movimenti)) {
    echo '
<table class="table table-bordered table-hover table-condensed table-striped">
    <tr>
        <th>'.tr('Causale').'</th>
        <th width="100">'.tr('Data').'</th>
        <th width="100">'.tr('Dare').'</th>
        <th width="100">'.tr('Avere').'</th>
        <th width="100">'.tr('Scalare').'</th>
    </tr>';

    $scalare = 0;

    // Elenco righe del partitario
    foreach ($movimenti as $movimento) {
        echo '
    <tr>
        <td>';

        $modulo_fattura = ($movimento['dir'] == 'entrata') ? module('Fatture di vendita') : module('Fatture di acquisto');

        if (!empty($movimento['primanota'])) {
            echo Modules::link($prima_nota->id, $movimento['idmastrino'], $movimento['descrizione']);
        } else {
            echo Modules::link($modulo_fattura->id, $movimento['iddocumento'], $movimento['descrizione']);
        }

        echo '
        </td>';

        // Data
        echo '
        <td>
            '.dateFormat($movimento['data']).'
        </td>';

        // Dare
        if ($movimento['totale'] > 0) {
            echo '
        <td class="text-right">
            '.moneyFormat(abs($movimento['totale']), 2).'
        </td>
        <td></td>';
        }

        // Avere
        else {
            echo '
        <td></td>
        <td class="text-right">
            '.moneyFormat(abs($movimento['totale']), 2).'
        </td>';
        }

        $scalare += $movimento['totale'];

        echo '
        <td class="text-right">
            '.moneyFormat($scalare, 2).'
        </td>';

        echo '
    </tr>';
    }

    echo '
</table>

<script>
function open_movimento(id_mastrino, id_module){
    launch_modal("'.tr('Dettagli movimento').'", "'.$structure->fileurl('dettagli_movimento.php').'?id_mastrino=" + id_mastrino + "&id_module=" + id_module);
}
</script>';
} else {
    echo '
<span>'.tr('Nessun movimento presente').'</span>';
}
