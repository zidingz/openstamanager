<?php

namespace Modules\Retro\Controllers;

use Modules\Retro\Parser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Classe dedicata alla gestione delle informazioni relative alla schermata princiaple di un modulo specifico.
 *
 * @since 2.5
 */
class ModuleController extends Parser
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = $this->controller($args);

        $template = filter('modal') !== null ? 'add' : 'controller';

        return $this->twig->render($response, '@resources/retro/'.$template.'.twig', $args);
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
        $args = parent::add($args);

        $args['query'] = $request->getQueryParams();

        return $this->twig->render($response, '@resources/retro/add.twig', $args);
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
