<?php

namespace Middlewares\Authorization;

use Middlewares\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;

/**
 * Classe per il controllo sui permessi di accesso relativi alle diverse sezioni del gestionale.
 *
 * @since 2.5
 */
class PermissionMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->getRoute($request);
        if (empty($route)) {
            return $handler->handle($request);
        }

        $args = $route->getArguments();

        $structure = $args['module'];

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
            //throw new HttpNotFoundException($request);
        } else {
        }

        return $handler->handle($request);
    }
}
