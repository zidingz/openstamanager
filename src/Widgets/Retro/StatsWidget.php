<?php

namespace Widgets\Retro;

use Widgets\StatsWidget as Original;

class StatsWidget extends Original
{
    public function getQuery(): string
    {
        return $this->widget['query'] ?: 'SELECT 0 AS dato';
    }

    protected function getAttributes(): string
    {
        $attributes = parent::getAttributes();
        $js = $this->widget['more_link'];

        if (!empty($js)) {
            return $attributes.' onclick="'.$js.'"';
        }

        return $attributes;
    }

    protected function getTitle(): string
    {
        return $this->widget['text'];
    }
}
