<?php

namespace Modules\Fatture\Controllers;

use Modules\Retro\RecordController;
use Modules\Traits\RowTrait;
use Modules\Fatture\Fattura;

class Record extends RecordController
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
