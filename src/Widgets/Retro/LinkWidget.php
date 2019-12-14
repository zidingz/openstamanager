<?php

namespace Widgets\Retro;

use Widgets\LinkWidget as Original;

class LinkWidget extends Original
{
    public function getLink(): string
    {
        return $this->widget['more_link'];
    }

    protected function getTitle(): string
    {
        return $this->widget['text'];
    }

    protected function getContent(): string
    {
        return '';
    }
}
