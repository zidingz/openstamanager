<?php

namespace Widgets\Retro;

use Middlewares\Authorization\UserMiddleware;
use Modules\Module;
use Slim\App as SlimApp;
use Widgets\ModalWidget as Original;

class ModalWidget extends Original
{
    public function getModal(): string
    {
        $content = '';

        $widget = $this->model;
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
        return $this->model['text'] ?: '';
    }

    protected function getContent(): string
    {
        $content = '';

        $widget = $this->model;
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

            $content = preg_match('/\\d/', $value) ? $value : '-';
        }

        return $content;
    }

    public function getLink(): string
    {
        $id = $this->model->id;

        return ROOTDIR.'/widget/modal/'.$id;
    }

    protected function routes(SlimApp $app): void
    {
        $id = $this->model->id;
        $app->get('/widget/modal/'.$id, ModalController::class.':modal')
            ->add(UserMiddleware::class);
    }
}
