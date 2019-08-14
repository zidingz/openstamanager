<?php

namespace Controllers\Retro;

use Managers\ModuleInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ModuleController extends RetroController implements ModuleInterface
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->controller($args);

        return $this->twig->render($response, 'old/controller.twig', $args);
    }

    public function modal(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->editor($args);

        return $this->twig->render($response, 'old/add.twig', $args);
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = parent::add($args);

        return $this->twig->render($response, 'old/add.twig', $args);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        return $this->actions($args);
    }
}
