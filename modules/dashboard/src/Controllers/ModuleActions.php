<?php

namespace Modules\Dashboard\Controllers;

use Managers\ControllerManager;

class ModuleActions extends ControllerManager
{
    public function manage($action, $request, $response, $args)
    {
        extract($args);

        $dbo = $database = $this->database;

        ob_start();
        // Registrazione del record
        $actions = $args['structure']->filepath('actions.php');
        include $actions;
        $result = ob_get_clean();

        $response->write($result);

        return $response;
    }
}
