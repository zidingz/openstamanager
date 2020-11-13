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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware per l'implementazione del periodo temporale.
 *
 * @since 2.5
 */
class CalendarMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Calendario
        // Periodo di visualizzazione
        if (!empty($_GET['period_start'])) {
            $_SESSION['period_start'] = $_GET['period_start'];
            $_SESSION['period_end'] = $_GET['period_end'];
        }
        // Dal 01-01-yyy al 31-12-yyyy
        elseif (!isset($_SESSION['period_start'])) {
            $_SESSION['period_start'] = date('Y').'-01-01';
            $_SESSION['period_end'] = date('Y').'-12-31';
        }

        $this->addVariable('calendar', [
            'start' => $_SESSION['period_start'],
            'end' => $_SESSION['period_end'],
            'is_current' => ($_SESSION['period_start'] != date('Y').'-01-01' || $_SESSION['period_end'] != date('Y').'-12-31'),
        ]);

        return $handler->handle($request);
    }
}
