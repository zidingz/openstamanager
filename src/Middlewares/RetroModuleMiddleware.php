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

use Models\OperationLog;
use Modules\Module;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Update;
use Util\Query;

/**
 * Middleware per il blocco dei plugin senza riferimento al record genitore.
 *
 * @since 2.5
 */
class RetroModuleMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->getRoute($request);
        if (empty($route) || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $handler->handle($request);
        }

        $name = $route->getName();
        $module_id = explode('-', $name)[0];

        $args = $route->getArguments();
        $args['module_id'] = $args['module_id'] ?: $module_id;

        Module::setCurrent($args['module_id']);
        Query::setModuleRecord($args['reference_id']);

        // Variabili fondamentali
        $module = Module::getCurrent();

        $args['id_module'] = $module['id'];
        $args['id_record'] = $args['record_id'];

        $args['structure'] = $module;
        $args['module'] = $module;

        // Argomenti di ricerca dalla sessione
        $this->addVariable('search', getSessionSearch($module['id']));

        // Gestione della visualizzazione plugin (reference_id obbligatorio)
        if (!empty($args['module']) && $args['module']->type == 'record_plugin' && !isset($args['reference_id'])) {
            throw new HttpNotFoundException($request);
        }

        // Impostazione degli argomenti
        $request = $this->setArgs($request, $args);

        // Informazioni estese sulle azioni dell'utente
        $op = $this->filter->post('op');
        if (!empty($op)) {
            OperationLog::setInfo('id_module', $args['id_module']);
            OperationLog::setInfo('id_record', $args['id_record']);

            OperationLog::build($op);
        }

        return $handler->handle($request);
    }
}
