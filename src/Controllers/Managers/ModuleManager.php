<?php

namespace Controllers\Managers;

abstract class ModuleManager extends ControllerManager
{
    abstract public function page($request, $response, $args);

    abstract public function add($request, $response, $args);

    abstract public function create($request, $response, $args);
}
