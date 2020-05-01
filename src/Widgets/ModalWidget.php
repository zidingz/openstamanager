<?php

namespace Widgets;

abstract class ModalWidget extends Manager
{
    abstract public function getModal(): string;
    abstract public function getLink(): string;

    protected function getAttributes(): string
    {
        $title = $this->getTitle();

        return 'data-href="'.$this->getLink().'" data-toggle="modal" data-title="'.$title.'"';
    }
}
