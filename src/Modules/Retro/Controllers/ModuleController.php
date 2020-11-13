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

namespace Modules\Retro\Controllers;

use Modules\Retro\Parser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Classe dedicata alla gestione delle informazioni relative alla schermata princiaple di un modulo specifico.
 *
 * @since 2.5
 */
class ModuleController extends Parser
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = $this->controller($args);

        $template = filter('modal') !== null ? 'add' : 'controller';

        return $this->twig->render($response, '@resources/retro/'.$template.'.twig', $args);
    }

    public function content(array $args)
    {
        $args = $this->prepare($args);
        $args = $this->controller($args);

        return $args['content'];
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = parent::add($args);

        $args['query'] = $request->getQueryParams();

        return $this->twig->render($response, '@resources/retro/add.twig', $args);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        ob_start();
        $id_record = $this->actions($args);
        $content = ob_get_clean();

        $response->write($content);

        $params = [
            'record_id' => $id_record,
        ];

        if (!isAjaxRequest()) {
            $path = $args['module']->url('record', $params);

            $response = $response->withRedirect($path);
        }

        return $response;
    }
}
