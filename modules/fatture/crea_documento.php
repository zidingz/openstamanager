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

use Modules\Fatture\Fattura;

$documento = Fattura::find($id_record);
$tipo_documento_finale = Fattura::class;

$options = [
    'type' => 'nota_credito',
    'op' => 'nota_credito',
    'serials' => true,
    'module' => 'Fatture di vendita',
    'documento' => $documento,
    'tipo_documento_finale' => $tipo_documento_finale,
    'button' => tr('Aggiungi'),
    'create_document' => true,
    'allow-empty' => true,
];

echo AppLegacy::load('importa.php', [], $options, true);
