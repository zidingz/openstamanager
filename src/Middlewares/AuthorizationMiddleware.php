<?php

namespace Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @since 2.5
 */
abstract class AuthorizationMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (!$this->hasAuthorization($request)) {
            $response = $this->operation($request, $response);
        } else {
            $response = $next($request, $response);
        }

        return $response;
    }

    abstract protected function operation(ServerRequestInterface $request, ResponseInterface $response);

    abstract protected function hasAuthorization($request);
}
