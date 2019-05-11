<?php

namespace Modules\Dashboard\Controllers;

use Managers\CustomManager;
use Managers\RetroController;

class Module extends CustomManager
{
    public function page($request, $response, $args)
    {
        $controller = new RetroController($this->container);

        $response = $controller->controller($request, $response, $args);

        return $response;
    }
}
