<?php

namespace Controllers\Retro;

use Managers\ModuleInterface;

class ModuleController extends RetroController implements ModuleInterface
{
    public function page($request, $response, $args)
    {
        $response = $this->controller($request, $response, $args);

        return $response;
    }

    public function dialog($request, $response, $args)
    {
        $response = $this->controller($request, $response, $args);

        return $response;
    }

    public function add($request, $response, $args)
    {
        $response = parent::add($request, $response, $args);

        return $response;
    }

    public function create($request, $response, $args)
    {
        return $this->actions($request, $response, $args);
    }
}
