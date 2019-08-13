<?php

namespace Modules\Fatture\Controllers;

use Managers\RecordManager;
use Managers\RowTrait;
use Modules\Fatture\Fattura;

class Record extends RecordManager
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
