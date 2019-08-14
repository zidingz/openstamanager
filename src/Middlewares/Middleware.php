<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * @since 2.5
 */
abstract class Middleware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        if (isset($this->container[$property])) {
            return $this->container[$property];
        }
    }

    abstract public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next);

    protected function addArgs(ServerRequestInterface $request, $new)
    {
        $route = $request->getAttribute('route');
        if (!$route) {
            return $request;
        }

        $original = $route->getArguments();
        $args = array_merge($original, $new);

        return $this->setArgs($request, $args);
    }

    protected function setArgs(ServerRequestInterface $request, $args)
    {
        $route = $request->getAttribute('route');

        // update the request with the new arguments to route
        $route->setArguments($args);
        $request = $request->withAttribute('route', $route);

        // also update the routeInfo attribute so that we are consistent
        $routeInfo = $request->getAttribute('routeInfo');
        $routeInfo[2] = $args;
        $request = $request->withAttribute('route', $route);

        return $request;
    }

    protected function addVariable($name, $content)
    {
        $twig = $this->container['twig'];
        $twig->offsetSet($name, $content);
    }
}
