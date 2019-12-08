<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @since 2.5
 */
abstract class AuthorizationMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if (!$this->hasAuthorization($request)) {
            $response = $this->operation($request);
        } else {
            $response = $handler->handle($request);
        }

        return $response;
    }

    abstract protected function operation(ServerRequestInterface $request);

    abstract protected function hasAuthorization($request);
}
