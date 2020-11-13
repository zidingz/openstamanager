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

namespace Widgets\Retro;

use Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Widgets\Widget;

/**
 * Controller dedicato alla gestione dei modal per i widget in retrocompatibilità.
 */
class ModalController extends Controller
{
    public function modal(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $path = $request->getUri()->getPath();
        $pieces = explode('/', $path);
        $id = end($pieces);

        $widget = Widget::find($id);
        $class = $widget->getManager();

        $result = $class->getModal();
        $response = $response->write($result);

        return $response;
    }
}
