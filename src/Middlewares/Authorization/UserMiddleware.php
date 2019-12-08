<?php

namespace Middlewares\Authorization;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

/**
 * @since 2.5
 */
class UserMiddleware extends \Middlewares\AuthorizationMiddleware
{
    protected function operation(ServerRequestInterface $request)
    {
        throw new HttpNotFoundException($request);
    }

    protected function hasAuthorization($request)
    {
        return $this->auth->check();
    }
}
