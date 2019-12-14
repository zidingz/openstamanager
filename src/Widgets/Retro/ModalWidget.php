<?php

namespace Widgets\Retro;

use Modules\Module;
use Widgets\ModalWidget as Original;

class ModalWidget extends Original
{
    public function getModal(): string
    {
        $content = null;

        if (!empty($widget['more_link'])) {
            $database = $dbo = $this->getContainer()->get('database');

            ob_start();
            include DOCROOT.'/'.$widget['more_link'];
            $content = ob_get_clean();
        }

        return $content;
    }

    protected function getTitle(): string
    {
        return $this->widget['text'];
    }

    protected function getContent(): string
    {
        $content = '';

        $widget = $this->widget;
        if (!empty($widget['query'])) {
            $query = $widget['query'];
            $module = Module::get($widget['id_module']);
            $additionals = $module->getAdditionalsQuery();
            if (!empty($additionals)) {
                $query = str_replace('1=1', '1=1 '.$additionals, $query);
            }

            $query = \Util\Query::replacePlaceholder($query);

            // Individuazione del risultato della query
            $database = $this->getContainer()->get('database');
            $value = '-';
            if (!empty($query)) {
                $value = $database->fetchArray($query)[0]['dato'];
            }

            return preg_match('/\\d/', $value) ? $value : '-';
        } elseif (!empty($widget['more_link'])) {
            $database = $dbo = $this->getContainer()->get('database');

            $is_number_request = true;
            ob_start();
            include DOCROOT.'/'.$widget['more_link'];
            $content = ob_get_clean();
        }

        return $content;
    }
}
