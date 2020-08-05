<?php

namespace Modules\Anagrafiche\API\v1;

use API\Interfaces\RetrieveInterface;
use API\Request;

class Sedi extends Request implements RetrieveInterface
{
    public function retrieve($request)
    {
        return [
            'table' => 'an_sedi',
        ];
    }
}
