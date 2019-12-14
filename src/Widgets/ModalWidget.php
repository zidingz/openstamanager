<?php

namespace Widgets;

abstract class ModalWidget extends Manager
{
    abstract public function getModal(): string;

    protected function getAttributes(): string
    {
        $title = $this->getTitle();

        return 'data-href="'.$this->widget['more_link'].'" data-toggle="modal" data-title="'.$title.'"';
    }
}
