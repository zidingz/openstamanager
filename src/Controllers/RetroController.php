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

use Middlewares\ContentMiddleware;
use Modules\Module;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class RetroController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $require_path = realpath(DOCROOT.'/'.$args['path']);

        if (empty($args['path']) || $require_path == DOCROOT.'/index.php') {
            redirect(ROOTDIR);
            exit();
        }

        if (!string_starts_with($require_path, DOCROOT) || !file_exists($require_path) || !is_file($require_path)) {
            throw new HttpNotFoundException($request);
        }

        $content = $this->execute($require_path, $args);

        return $response->write($content);
    }

    protected function execute($require_path, $args)
    {
        extract($args);

        // Impostazione dinamica del menu
        $args['main_menu'] = ContentMiddleware::getMainMenu();

        // Configurazione
        $config = $this->config;
        extract($config);

        $docroot = DOCROOT;
        $rootdir = ROOTDIR;

        // Moduli
        $dbo = $database = $this->database;
        $id_module = $this->filter->getValue('id_module');
        $id_record = $this->filter->getValue('id_record');
        $id_parent = $this->filter->getValue('id_parent');

        Module::setCurrent($id_module);
        $module = $structure = Module::getCurrent();
        $plugin = null;

        // Pagina diretta
        ob_start();
        require $require_path;
        $content = ob_get_clean();

        return $content;
    }
}
