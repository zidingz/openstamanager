<?php

namespace Modules\Fatture\Controllers;

use Modules\Fatture\Fattura;
use Modules\Retro\RecordController;
use Modules\Traits\RowTrait;

class RecordActions extends RecordController
{
    use RowTrait;

    protected $rowOptions = [
        'show-conto' => true,
    ];

    private function getMainClass()
    {
        return Fattura::class;
    }
}
