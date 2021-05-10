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

$link_id = Modules::get('Articoli')['id'];

$fields = [
    'Codice' => 'codice',
    'Barcode' => 'barcode',
    'Descrizione' => 'descrizione',
    'Categoria' => '(SELECT nome FROM mg_categorie WHERE mg_categorie.id =  mg_articoli.id_categoria)',
    'Subcategoria' => '(SELECT nome FROM mg_categorie WHERE mg_categorie.id =  mg_articoli.id_sottocategoria)',
    'Note' => 'note',
];

$query = 'SELECT *';

foreach ($fields as $name => $value) {
    $query .= ', '.$value." AS '".str_replace("'", "\'", $name)."'";
}

$query .= ' FROM mg_articoli WHERE 1=0 ';

foreach ($fields as $name => $value) {
    $query .= ' OR '.$value.' LIKE "%'.$term.'%"';
}

$query .= Modules::getAdditionalsQuery('Articoli');

$rs = $dbo->fetchArray($query);

foreach ($rs as $r) {
    $result = [];

    $result['link'] = base_url().'/editor.php?id_module='.$link_id.'&id_record='.$r['id'];
    $result['title'] = $r['codice'].' - '.$r['descrizione'];
    $result['category'] = 'Articoli';

    // Campi da evidenziare
    $result['labels'] = [];
    foreach ($fields as $name => $value) {
        if (string_contains($r[$name], $term)) {
            $text = str_replace($term, "<span class='highlight'>".$term.'</span>', $r[$name]);

            $result['labels'][] = $name.': '.$text.'<br/>';
        }
    }

    // Aggiunta nome anagrafica come ultimo campo
    if (sizeof($ragioni_sociali) > 1) {
        $result['labels'][] = 'Anagrafica: '.$ragioni_sociali[$r['idanagrafica']].'<br/>';
    }

    $results[] = $result;
}
