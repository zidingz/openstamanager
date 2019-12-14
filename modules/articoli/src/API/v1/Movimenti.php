<?php

namespace Modules\Articoli\API\v1;

use API\Interfaces\CreateInterface;
use API\Request;
use Modules\Articoli\Articolo;

class Movimenti extends Request implements CreateInterface
{
    public function create($request)
    {
        $data = $request['data'];

        $articolo = Articolo::find($data['id_articolo']);
        $articolo->movimenta($data['qta'], $data['descrizione'], $data['data'], true);
    }
}
