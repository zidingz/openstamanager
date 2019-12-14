<?php

namespace Widgets;

use Components\Component;
use Slim\App as SlimApp;

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
            <span aria-d-none="true">&times;</span><span class="sr-only">'.tr('Chiudi').'</span>
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
