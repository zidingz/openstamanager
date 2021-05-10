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

namespace API\App\v1;

use API\Interfaces\CreateInterface;
use API\Resource;
use API\Response;
use Auth;
use Update;

class Login extends Resource implements CreateInterface
{
    public function create($request)
    {
        $database = database();

        // Controllo sulle credenziali
        if (auth()->once(['username' => $request['username'], 'password' => $request['password']])) {
            $user = $this->getUser();

            $tokens = $user->getApiTokens();
            $token = $tokens[0]['token'];

            // Informazioni sull'utente, strettamente collegato ad una anagrafica di tipo Tecnico
            $utente = $database->fetchOne("SELECT
                `an_anagrafiche`.`idanagrafica` AS id_anagrafica,
                `an_anagrafiche`.`ragione_sociale`
            FROM `zz_users`
                INNER JOIN `an_anagrafiche` ON `an_anagrafiche`.`idanagrafica` = `zz_users`.`idanagrafica`
                INNER JOIN an_tipianagrafiche_anagrafiche ON an_tipianagrafiche_anagrafiche.idanagrafica = an_anagrafiche.idanagrafica
                INNER JOIN an_tipianagrafiche ON an_tipianagrafiche_anagrafiche.idtipoanagrafica = an_tipianagrafiche.idtipoanagrafica
            WHERE an_tipianagrafiche.descrizione = 'Tecnico' AND `an_anagrafiche`.`deleted_at` IS NULL AND `id` = :id", [
                ':id' => $user['id'],
            ]);

            if (!empty($utente)) {
                // Informazioni da restituire tramite l'API
                $response = [
                    'id_anagrafica' => (string) $utente['id_anagrafica'],
                    'ragione_sociale' => $utente['ragione_sociale'],
                    'token' => $token,
                    'version' => Update::getVersion(),
                ];
            } else {
                $response = [
                    'status' => Response::getStatus()['unauthorized']['code'],
                ];
            }
        } else {
            $response = [
                'status' => Response::getStatus()['unauthorized']['code'],
            ];

            // Se è in corso un brute-force, aggiunge il timeout
            if (auth()->isBrute()) {
                $response['timeout'] = auth()->getBruteTimeout();
            }
        }

        return $response;
    }
}
