<?php

namespace Managers;

interface PageInterface
{
    public function page($request, $response, $args);

    public function dialog($request, $response, $args);
}
