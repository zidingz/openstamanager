<?php

namespace Widgets;

use Controllers\Controller;
use Models\Widget;
use Psr\Container\ContainerInterface;

abstract class Manager extends Controller
{
    protected $widget;
    protected $record_id;

    public function __construct(ContainerInterface $container, Widget $widget, ?int $record_id = null)
    {
        parent::__construct($container);

        $this->widget = $widget;
        $this->record_id = $record_id;
    }

    abstract protected function getTitle(): string;

    abstract protected function getContent(): string;

    protected function getAttributes(): string
    {
        return 'href="#"';
    }

    protected function render()
    {
        $widget = $this->widget;

        $title = $this->getTitle();
        $content = $this->getContent();

        $result = '
<a class="clickable" '.$this->getAttributes().'>
    <div class="info-box">
        <button type="button" class="close" onclick="if(confirm(\'Disabilitare questo widget?\')) { $.post( \''.ROOTDIR.'/actions.php?id_module='.self::getModule()->id.'\', { op: \'disable_widget\', id: \''.$widget['id'].'\' }, function(response){ location.reload(); }); };" >
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
}
