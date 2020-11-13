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

use Models\Template;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PrintController extends Controller
{
    public function view(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $link = urlFor('print-open', [
            'print_id' => $args['print_id'],
            'record_id' => $args['record_id'],
        ]);
        $args['link'] = $request->getUri()->getBasePath().'/assets/pdfjs/web/viewer.html?file='.$link;

        $response = $this->twig->render($response, '@resources/uploads/frame.twig', $args);

        return $response;
    }

    public function open(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $print = Template::find($args['print_id']);
        $manager = $print->getManager($this->container, $args['record_id']);

        $pdf = $manager->render();

        $response = $response
            ->withHeader('Content-Type', 'application/pdf')
            ->write($pdf);

        return $response;
    }
}
