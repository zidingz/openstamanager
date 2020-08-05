<?php

namespace API\Common;

use API\Interfaces\RetrieveInterface;
use API\Request;
use Prints;
use Prints\Template;

class Stampa extends Request implements RetrieveInterface
{
    public function retrieve($request)
    {
        $print = Template::where('name', $request['name'])->first();
        if (!empty($print)) {
            $directory = DOCROOT.'/files/api';
            $data = Prints::render($print->id, $request['id_record'], $directory);

            download($data['path']);
            delete($data['path']);
        }

        return [
            'custom' => '',
        ];
    }
}
