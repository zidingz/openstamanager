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
 * Classe dedicata alla gestione delle informazioni relative al record di un modulo specifico.
 *
 * @since 2.5
 */
class RecordController extends Parser
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = $this->editor($args);

        $template = filter('modal') !== null ? 'add' : 'editor';

        return $this->twig->render($response, '@resources/retro/'.$template.'.twig', $args);
    }

    public function content(array $args)
    {
        $args = $this->prepare($args);
        $args = $this->editor($args);

        $args['content'] = $args['editor_content'];

        return $args;
    }

    public function data($id_record)
    {
        $dbo = $database = $this->database;
        $defined_vars = get_defined_vars();

        // Lettura risultato query del modulo
        $init = $this->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $vars = get_defined_vars();

        $result = array_diff_key($vars, $defined_vars);
        unset($result['defined_vars']);
        unset($result['init']);

        return $result;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->actions($args);

        return $response->withRedirect($args['module']->url('record', $args));
    }
}
