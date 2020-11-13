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

use Middlewares\Authorization\UserMiddleware;
use Modules\Module;
use Slim\App as SlimApp;
use Util\Query;
use Widgets\ModalWidget as Original;

class ModalWidget extends Original
{
    public function getModal(): string
    {
        $content = '';

        $widget = $this->model;
        if (!empty($widget['more_link'])) {
            $database = $dbo = $this->getContainer()->get('database');

            ob_start();
            include DOCROOT.'/'.$widget['more_link'];
            $content = ob_get_clean();
        }

        return $content;
    }

    public function getLink(): string
    {
        $id = $this->model->id;

        return ROOTDIR.'/widget/modal/'.$id;
    }

    protected function getTitle(): string
    {
        return $this->model['text'] ?: '';
    }

    protected function getContent(): string
    {
        $content = '';

        $widget = $this->model;
        if (!empty($widget['query'])) {
            $query = $widget['query'];
            $module = Module::pool($widget['id_module']);
            $additionals = $module->getAdditionalsQuery();
            if (!empty($additionals)) {
                $query = str_replace('1=1', '1=1 '.$additionals, $query);
            }

            $query = Query::replacePlaceholder($query);

            // Individuazione del risultato della query
            $database = $this->getContainer()->get('database');
            $value = '-';
            if (!empty($query)) {
                $value = $database->fetchArray($query)[0]['dato'];
            }

            $content = preg_match('/\\d/', $value) ? $value : '-';
        }

        return $content;
    }

    protected function routes(SlimApp $app): void
    {
        $id = $this->model->id;
        $app->get('/widget/modal/'.$id, ModalController::class.':modal')
            ->add(UserMiddleware::class);
    }
}
