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

namespace Middlewares\Authorization;

use Middlewares\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;

/**
 * Classe per il controllo sui permessi di accesso relativi alle diverse sezioni del gestionale.
 *
 * @since 2.5
 */
class PermissionMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->getRoute($request);
        if (empty($route)) {
            return $handler->handle($request);
        }

        $args = $route->getArguments();

        $structure = $args['module'];

        // Controllo sui permessi di accesso alla struttura
        $enabled = ['r', 'rw'];
        $permission = in_array($structure->permission, $enabled);

        // Controllo sui permessi di accesso al record
        if (!empty($args['record_id'])) {
            $permission &= $structure->hasRecordAccess($args['record_id']);
        }

        if (!$permission) {
            //$response = $this->twig->render($response, 'errors\403.twig', $args);
            //return $response->withStatus(403);
            //throw new HttpNotFoundException($request);
        } else {
        }

        return $handler->handle($request);
    }
}
