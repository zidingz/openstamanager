<?php

namespace Managers;

interface PageInterface
{
    public function page($request, $response, $args);

    public function content($request, $response, $args);
}
