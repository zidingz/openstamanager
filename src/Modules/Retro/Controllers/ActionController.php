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

/**
 * Classe dedicata alla sostituzione delle chiamate AJAX implicite per i contenuti di un modulo specifico.
 *
 * @since 2.5
 */
class ActionController extends Parser
{
    public function __call($name, $arguments)
    {
        $action = $arguments[2]['action'];
        $action = str_replace(['-', '_'], [' ', ' '], $action);
        $action = lcfirst(ucwords($action));
        $action = str_replace(' ', '', $action);

        $op = filter('op');

        if (empty($op)) {
            $this->filter->set('get', 'op', $action);
            $this->filter->set('post', 'op', $action);
        }

        ob_start();
        $this->actions($arguments[2]);
        $result = ob_get_clean();

        $arguments[1]->write($result);

        return $arguments[1];
    }
}
