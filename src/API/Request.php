<?php

namespace API;

class Request
{
    public function open($request)
    {
    }

    public function close($request, $response)
    {
    }

    public function getUser()
    {
        return auth()->getUser();
    }
}
