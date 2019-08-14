<?php

namespace Middlewares\Authorization;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @since 2.5
 */
class UserMiddleware extends \Middlewares\AuthorizationMiddleware
{
    protected function operation(ServerRequestInterface $request, ResponseInterface $response)
    {
        throw new \Slim\Exception\NotFoundException($request, $response);
    }

    protected function hasAuthorization($request)
    {
        return $this->auth->check();
    }
}
