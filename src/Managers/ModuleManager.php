<?php

namespace Managers;

abstract class ModuleManager extends CustomManager
{
    abstract public function add($request, $response, $args);

    abstract public function create($request, $response, $args);
}
