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

$r = $dbo->fetchOne('SELECT *,
    IF( (an_referenti.email IS NOT NULL AND an_referenti.email!=""), an_referenti.email, an_anagrafiche.email) AS email,
    an_anagrafiche.ragione_sociale
FROM co_contratti INNER JOIN an_anagrafiche ON co_contratti.idanagrafica=an_anagrafiche.idanagrafica LEFT OUTER JOIN an_referenti ON an_referenti.id=co_contratti.idreferente WHERE co_contratti.id='.prepare($id_record));

// Variabili da sostituire
return [
    'email' => $r['email'],
    'ragione_sociale' => $r['ragione_sociale'],
    'numero' => $r['numero'],
    'descrizione' => $r['descrizione'],
    'data' => dateFormat($r['data_bozza']),
    'id_anagrafica' => $r['idanagrafica'],
];
