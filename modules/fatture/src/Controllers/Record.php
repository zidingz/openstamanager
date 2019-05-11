<?php

namespace Modules\Fatture\Controllers;

use Managers\RecordManager;
use Managers\RetroController;
use Managers\RowTrait;
use Modules\Fatture\Fattura;

class Record extends RecordManager
{
    use RowTrait;

    protected $rowOptions = [
        'show-conto' => true,
    ];

    public function page($request, $response, $args)
    {
        $controller = new RetroController($this->container);

        $response = $controller->editor($request, $response, $args);

        return $response;
    }

    public function update($request, $response, $args)
    {
        $controller = new RetroController($this->container);

        $response = $controller->actions($request, $response, $args);

        return $response;
    }

    private function getMainClass()
    {
        return Fattura::class;
    }
}
