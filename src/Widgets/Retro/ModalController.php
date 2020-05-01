<?php

namespace Widgets\Retro;

use Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Widgets\Widget;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller dedicato alla gestione dei modal per i widget in retrocompatibilità.
 */
class ModalController extends Controller
{
    public function modal(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $path = $request->getUri()->getPath();
        $pieces = explode('/', $path);
        $id = end($pieces);

        $widget = Widget::find($id);
        $class = $widget->getManager();

        $result = $class->getModal();
        $response = $response->write($result);

        return $response;
    }
}
