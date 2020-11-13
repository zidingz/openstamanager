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

namespace Widgets;

use Components\Component;
use Slim\App as SlimApp;

/**
 * Classe dedicata alla gestione di base dei widget del gestionale.
 * Introduce un rendering di base e definisce i comporamenti standard da estendere per un utilizzo più completo.
 *
 * @since 2.5
 */
abstract class Manager extends Component
{
    protected $record_id;

    public function setRecord(?int $record_id = null)
    {
        $this->record_id = $record_id;
    }

    public function render(array $args = []): string
    {
        $widget = $this->model;

        $title = $this->getTitle();
        $content = $this->getContent();

        $result = '
<a class="clickable" '.$this->getAttributes().'>
    <div class="info-box">
        <button type="button" class="close" onclick="if(confirm(\'Disabilitare questo widget?\')) { $.post( \''.ROOTDIR.'/actions.php?id_module='.$widget->module->id.'\', { op: \'disable_widget\', id: \''.$widget['id'].'\' }, function(response){ location.reload(); }); };" >
            <span aria-hidden="true">&times;</span><span class="sr-only">'.tr('Chiudi').'</span>
        </button>

        <span class="info-box-icon" style="background-color:'.$widget['bgcolor'].'">';

        if (!empty($widget['icon'])) {
            $result .= '
            <i class="'.$widget['icon'].'"></i>';
        }

        $result .= '
        </span>

        <div class="info-box-content">
            <span class="info-box-text'.(!empty($widget['help']) ? ' tip' : '').'"'.(!empty($widget['help']) ? ' title="'.prepareToField($widget['help']).'" data-position="bottom"' : '').'>
                '.$title.'

                '.(!empty($widget['help']) ? '<i class="fa fa-question-circle-o"></i>' : '').'
            </span>';

        if (isset($content)) {
            $result .= '
            <span class="info-box-number">'.$content.'</span>';
        }

        $result .= '
        </div>
    </div>
</a>';

        return $result;
    }

    public function updates(): array
    {
        return [];
    }

    abstract protected function getTitle(): string;

    abstract protected function getContent(): string;

    protected function getAttributes(): string
    {
        return 'href="#"';
    }

    /* Standard Widget */

    protected function autoload(): void
    {
    }

    protected function views(): void
    {
    }

    protected function routes(SlimApp $app): void
    {
    }
}
