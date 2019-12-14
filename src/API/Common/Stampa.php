<?php

namespace Api\Common;

use API\Interfaces\RetrieveInterface;
use API\Request;
use Models\Template;
use Prints;

class Stampa extends Request implements RetrieveInterface
{
    public function retrieve($request)
    {
        $content = '';

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
