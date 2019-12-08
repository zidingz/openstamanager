<?php

namespace Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteContext;

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
        if ($this->container->has($property)) {
            return $this->container->get($property);
        }
    }

    abstract public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler);

    protected function addArgs(ServerRequestInterface $request, $new)
    {
        $route = $this->getRoute($request);
        if (empty($route)) {
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
        $twig = $this->container->get('twig');
        $twig->offsetSet($name, $content);
    }

    protected function getRoute(ServerRequestInterface $request){
        $routeContext = RouteContext::fromRequest($request);
        return $routeContext->getRoute();
    }
}
