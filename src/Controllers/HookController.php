<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.n.c.
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

namespace Controllers;

use Hooks\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HookController extends Controller
{
    public function hooks(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hooks = Hook::all();

        $results = [];
        foreach ($hooks as $hook) {
            $results[] = [
                'id' => $hook->id,
                'name' => $hook->name,
            ];
        }

        $response = $response->write(json_encode($results));

        return $response;
    }

    public function lock(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hook_id = $args['hook_id'];
        $hook = Hook::find($hook_id);

        $token = $hook->lock();
        $response = $response->write(json_encode($token));

        return $response;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hook_id = $args['hook_id'];
        $token = $args['token'];
        $hook = Hook::find($hook_id);

        $results = $hook->execute($token);
        $response = $response->write(json_encode($results));

        return $response;
    }

    public function response(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hook_id = $args['hook_id'];
        $hook = Hook::find($hook_id);

        $results = $hook->response();
        $response = $response->write(json_encode($results));

        return $response;
    }
}
