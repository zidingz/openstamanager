<?php

namespace Middlewares\Authorization;

use Middlewares\Middleware;
use Slim\Exception\NotFoundException;
use Util\Query;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Classe per il controllo sui permessi di accesso relativi alle diverse sezioni del gestionale.
 *
 * @since 2.5
 */
class PermissionMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $route = $request->getAttribute('route');
        if (!$route) {
            return $next($request, $response);
        }

        $args = $route->getArguments();

        $structure = $args['structure'];

        // Controllo sui permessi di accesso alla struttura
        $enabled = ['r', 'rw'];
        $permission = in_array($structure->permission, $enabled);

        // Controllo sui permessi di accesso al record
        if (!empty($args['record_id'])) {
            $permission &= $structure->hasRecordAccess($args['record_id']);
        }

        if (!$permission) {
            //$response = $this->twig->render($response, 'errors\403.twig', $args);
            //return $response->withStatus(403);
            throw new NotFoundException($request, $response);
        } else {
            $response = $next($request, $response);
        }

        return $response;
    }
}
