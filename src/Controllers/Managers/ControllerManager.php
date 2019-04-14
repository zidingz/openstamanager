<?php

namespace Controllers\Managers;

use Controllers\Controller;
use Slim\Exception\NotFoundException;

abstract class ControllerManager extends Controller
{
    public function manage($action, $request, $response, $args)
    {
        if (!method_exists($this, $action)) {
            throw new NotFoundException($request, $response);
        }

        $response = $this->{$action}($request, $response, $args);

        return $response;
    }
}
