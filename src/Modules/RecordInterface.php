<?php

namespace Modules;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RecordInterface extends PageInterface
{
    public function data($id_record);

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args);
}
