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

namespace HTMLBuilder\Manager;

use Modules\Module;
use Widgets\Widget;

/**
 * @since 2.4
 */
class WidgetManager implements ManagerInterface
{
    public function manage($options)
    {
        $result = '';

        if (isset($options['id'])) {
            $result = $this->widget($options);
        } else {
            $result = $this->group($options);
        }

        return $result;
    }

    protected function widget($options)
    {
        $database = database();

        $widget = Widget::find($options['id']);

        return $widget->render();
    }

    protected static function getModule()
    {
        return module('Stato dei servizi');
    }

    protected function group($options)
    {
        $query = 'SELECT id FROM zz_widgets WHERE id_module = '.prepare($options['id_module']).' AND (|position|) AND enabled = 1 ORDER BY `order` ASC';

        // Mobile (tutti i widget a destra)
        if (isMobile()) {
            if ($options['position'] == 'right') {
                $position = "location = '".$options['place']."_right' OR location = '".$options['place']."_top'";
            } elseif ($options['position'] == 'top') {
                $position = '1=0';
            }
        }

        // Widget a destra
        elseif ($options['position'] == 'right') {
            $position = "location = '".$options['place']."_right'";
        }

        // Widget in alto
        elseif ($options['position'] == 'top') {
            $position = "location = '".$options['place']."_top'";
        }

        $query = str_replace('|position|', $position, $query);

        // Individuazione dei widget interessati
        $database = database();
        $widgets = $database->fetchArray($query);

        $result = ' ';

        // Generazione del codice HTML
        if (!empty($widgets)) {
            $row_max = count($widgets);
            if ($row_max > 4) {
                $row_max = 4;
            } elseif ($row_max < 2) {
                $row_max = 2;
            }

            $result = '
<ul class="row widget" id="widget-'.$options['position'].'" data-class="">';

            // Aggiungo ad uno ad uno tutti i widget
            foreach ($widgets as $widget) {
                $result .= '
    <li class="col-sm-6 col-md-4 col-lg-'.intval(12 / $row_max).' li-widget" id="widget_'.$widget['id'].'">';

                $info = array_merge($options, [
                    'id' => $widget['id'],
                ]);
                $result .= $this->widget($info);

                $result .= '
    </li>';
            }

            $result .= '
</ul>';
        }

        return $result;
    }
}
