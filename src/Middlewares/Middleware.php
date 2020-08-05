<?php

namespace Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteContext;

/**
 * @since 2.5
 */
abstract class Middleware implements MiddlewareInterface
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

    abstract public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

    protected function addArgs(ServerRequestInterface $request, $new): ServerRequestInterface
    {
        $route = $this->getRoute($request);
        if (empty($route)) {
            return $request;
        }

        $original = $route->getArguments();
        $args = array_merge($original, $new);

        return $this->setArgs($request, $args);
    }

    protected function setArgs(ServerRequestInterface $request, $args): ServerRequestInterface
    {
        $route = $this->getRoute($request);

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

    protected function getRoute(ServerRequestInterface $request): ?RouteInterface
    {
        $routeContext = RouteContext::fromRequest($request);

        return $routeContext->getRoute();
    }
}
