<?php

namespace Modules;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ModuleInterface extends PageInterface
{
    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args);

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args);
}
