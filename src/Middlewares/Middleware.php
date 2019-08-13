<?php

namespace Middlewares;

/**
 * @since 2.5
 */
abstract class Middleware
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        if (isset($this->container[$property])) {
            return $this->container[$property];
        }
    }

    abstract public function __invoke($request, $response, $next);

    protected function addArgs($request, $new)
    {
        $route = $request->getAttribute('route');
        if (!$route) {
            return $request;
        }

        $original = $route->getArguments();
        $args = array_merge($original, $new);

        return $this->setArgs($request, $args);
    }

    protected function setArgs($request, $args)
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
