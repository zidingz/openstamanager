<?php

namespace Widgets;

abstract class LinkWidget extends Manager
{
    abstract public function getLink(): string;

    protected function getAttributes(): string
    {
        return 'href="'.$this->getLink().'"';
    }
}
