<?php

namespace Managers;

abstract class CustomManager extends ControllerManager
{
    abstract public function page($request, $response, $args);
}
