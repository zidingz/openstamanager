<?php

namespace Managers;

abstract class RecordManager extends ControllerManager
{
    abstract public function page($request, $response, $args);

    abstract public function update($request, $response, $args);
}
