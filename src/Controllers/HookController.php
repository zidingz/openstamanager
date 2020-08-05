<?php

namespace Controllers;

use Hooks\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HookController extends Controller
{
    public function hooks(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hooks = Hook::all();

        $results = [];
        foreach ($hooks as $hook) {
            $results[] = [
                'id' => $hook->id,
                'name' => $hook->name,
            ];
        }

        $response = $response->write(json_encode($results));

        return $response;
    }

    public function lock(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hook_id = $args['hook_id'];
        $hook = Hook::find($hook_id);

        $token = $hook->lock();
        $response = $response->write(json_encode($token));

        return $response;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hook_id = $args['hook_id'];
        $token = $args['token'];
        $hook = Hook::find($hook_id);

        $results = $hook->execute($token);
        $response = $response->write(json_encode($results));

        return $response;
    }

    public function response(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $hook_id = $args['hook_id'];
        $hook = Hook::find($hook_id);

        $results = $hook->response();
        $response = $response->write(json_encode($results));

        return $response;
    }
}
