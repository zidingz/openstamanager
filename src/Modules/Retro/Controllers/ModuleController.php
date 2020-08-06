<?php

namespace Modules\Retro\Controllers;

use Modules\Retro\Parser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ModuleController extends Parser
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = $this->controller($args);

        $template = filter('modal') !== null ? 'add' : 'controller';

        return $this->twig->render($response, 'retro/'.$template.'.twig', $args);
    }

    public function content(array $args)
    {
        $args = $this->prepare($args);
        $args = $this->controller($args);

        return $args['content'];
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = parent::create($args);
        dd($request, $args);

        $args['query'] = $request->getQueryParams();

        return $this->twig->render($response, 'old/add.twig', $args);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        ob_start();
        $id_record = $this->actions($args);
        $content = ob_get_clean();

        $response->write($content);

        $params = [
            'record_id' => $id_record,
        ];

        if (!isAjaxRequest()) {
            $path = $args['module']->url('record', $params);

            $response = $response->withRedirect($path);
        }

        return $response;
    }
}
