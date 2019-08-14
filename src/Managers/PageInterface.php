<?php

namespace Managers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface PageInterface
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args);

    public function modal(ServerRequestInterface $request, ResponseInterface $response, array $args);
}
