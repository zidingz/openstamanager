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

/*
 * Header di default.
 * I contenuti di questo file vengono utilizzati per generare l'header delle stampe nel caso non esista un file header.php all'interno della stampa.
 *
 * Per modificare l'header della stampa basta aggiungere un file header.php all'interno della cartella della stampa con i contenuti da mostrare (vedasi templates/fatture/header.php).
 *
 * La personalizzazione specifica dell'header deve comunque seguire lo standard della cartella custom: anche se il file header.php non esiste nella stampa originaria, se si vuole personalizzare l'header bisogna crearlo all'interno della cartella custom.
 */
echo '
<h5 style="border-bottom:1px solid #777; display:block;">
    <div class="col-xs-5">STAMPA MASTRINO <small>'.dateFormat($date_start).' - '.dateFormat($date_end).'</small></div>
    <div class="col-xs-7 text-right">'.$azienda['ragione_sociale'].'</div>
</h5>
'.$conto1['descrizione'].'<br>';
if (get('lev') == '3') {
    echo '
    '.$conto2['numero'].' '.$conto2['descrizione'].'<br>
    '.$conto3['numero'].' '.$conto3['descrizione'].'<br>';
} elseif (get('lev') == '2') {
    echo '
    '.$conto2['numero'].' '.$conto2['descrizione'].'<br>';
}
