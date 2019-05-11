<?php

namespace Modules\Fatture\Controllers;

use Managers\ModuleManager;
use Managers\RetroController;

class Module extends ModuleManager
{
    public function page($request, $response, $args)
    {
        $controller = new RetroController($this->container);

        $response = $controller->controller($request, $response, $args);

        return $response;
    }

    public function add($request, $response, $args)
    {
        $controller = new RetroController($this->container);

        $response = $controller->add($request, $response, $args);

        return $response;
    }

    public function create($request, $response, $args)
    {
        $controller = new RetroController($this->container);

        $record_id = $controller->actions($request, $response, $args);

        return $record_id;
    }
}
