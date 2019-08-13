<?php

namespace Managers;

interface RecordInterface extends PageInterface
{
    public function data($id_record);

    public function update($request, $response, $args);
}
