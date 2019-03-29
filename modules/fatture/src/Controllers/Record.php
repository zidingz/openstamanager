<?php

namespace Modules\Fatture\Controllers;

use Controllers\ControllerManager;

class Record extends ControllerManager
{
    protected function rowAdd($request, $response, $args)
    {
        $response = $this->twig->render($response, 'common/riga.twig', $args);

        return $response;
    }
}
