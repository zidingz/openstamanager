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

use Modules\Ordini\Ordine;

$documento = Ordine::find($id_record);

$id_cliente = $documento['idanagrafica'];
$id_sede = $documento['idsede'];

$numero = !empty($documento['numero_esterno']) ? $documento['numero_esterno'] : $documento['numero'];
$pagamento = $dbo->fetchOne('SELECT * FROM co_pagamenti WHERE id = '.prepare($documento->idpagamento));

// Sostituzioni specifiche
$custom = [
    'tipo_doc' => Stringy\Stringy::create($documento->tipo->descrizione)->toUpperCase(),
    'numero' => $numero,
    'data' => dateFormat($documento['data']),
    'pagamento' => $pagamento['descrizione'],
];
