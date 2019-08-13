<?php

namespace Managers;

interface ModuleInterface extends PageInterface
{
    public function add($request, $response, $args);

    public function create($request, $response, $args);
}
