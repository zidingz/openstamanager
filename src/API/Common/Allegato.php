<?php

namespace Api\Common;

use API\Interfaces\CreateInterface;
use API\Request;
use Models\Upload;

class Allegato extends Request implements CreateInterface
{
    public function create($request)
    {
        $module = module($request['module']);

        $name = !empty($request['name']) ? $request['name'] : null;
        $category = !empty($request['category']) ? $request['category'] : null;

        $upload = Upload::build($_FILES['upload'], [
            'id_module' => $module['id'],
            'id_record' => $request['id'],
        ], $name, $category);

        return[
            'id' => $upload->id,
            'filename' => $upload->filename,
        ];
    }
}
