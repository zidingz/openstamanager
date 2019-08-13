<?php

namespace Controllers\Retro;

use Managers\ModuleInterface;

class ModuleController extends RetroController implements ModuleInterface
{
    public function page($request, $response, $args)
    {
        $args = $this->controller($args);

        return $this->twig->render($response, 'old/controller.twig', $args);
    }

    public function content($request, $response, $args)
    {
        $args = $this->editor($args);

        return $response->write($args['content']);
    }

    public function add($request, $response, $args)
    {
        $args = parent::add($args);

        return $this->twig->render($response, 'old/add.twig', $args);
    }

    public function create($request, $response, $args)
    {
        return $this->actions($args);
    }
}
