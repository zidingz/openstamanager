<?php

namespace Widgets\Retro;

use Widgets\StatsWidget as Original;

class StatsWidget extends Original
{
    public function getQuery(): string
    {
        return $this->model['query'] ?: 'SELECT 0 AS dato';
    }

    protected function getAttributes(): string
    {
        $attributes = parent::getAttributes();
        $js = $this->model['more_link'];

        if (!empty($js)) {
            return $attributes.' onclick="'.$js.'"';
        }

        return $attributes;
    }

    protected function getTitle(): string
    {
        return $this->model['text'];
    }
}
