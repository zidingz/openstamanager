<?php

namespace Middlewares;

use Slim\Exception\NotFoundException;
use Update;

/**
 * Middleware per il blocco dei plugin senza riferimento al record genitore.
 *
 * @since 2.5
 */
class PluginMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $route = $request->getAttribute('route');
        if (!$route || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $next($request, $response);
        }

        $args = $route->getArguments();

        if (!empty($args['module']) && $args['module']->type != 'module' && !isset($args['reference_id'])) {
            throw new NotFoundException($request, $response);
        }

        return $next($request, $response);
    }
}
