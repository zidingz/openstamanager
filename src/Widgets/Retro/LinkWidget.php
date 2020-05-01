<?php

namespace Widgets\Retro;

use Widgets\LinkWidget as Original;

class LinkWidget extends Original
{
    public function getLink(): string
    {
        return ROOTDIR.$this->model['more_link'];
    }

    protected function getTitle(): string
    {
        return $this->model['text'];
    }

    protected function getContent(): string
    {
        return '';
    }
}
