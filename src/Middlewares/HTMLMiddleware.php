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

namespace Middlewares;

use HTMLBuilder\HTMLBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream;

/**
 * Middlware per la gestione della lingua del progetto.
 *
 * @since 2.5
 */
class HTMLMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Versione e revisione
        $this->addVariable('version', \Update::getVersion());
        $this->addVariable('revision', \Update::getRevision());

        // Gestione HTML personalizzato
        $response = $handler->handle($request);
        $html = $response->getBody();

        $id_module = \Modules\Module::getCurrent()['id'];
        $html = str_replace('$id_module$', $id_module, $html);
        //$html = str_replace('$id_plugin$', $id_plugin, $html);
        //$html = str_replace('$id_record$', $id_record, $html);
        $html = HTMLBuilder::replace($html);

        $stream = fopen('php://temp', 'w');
        $body = new Stream($stream);
        $body->write($html);
        $response = $response->withBody($body);

        return $response;
    }
}
