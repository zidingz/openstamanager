<?php

namespace Modules\Retro;

use Modules\Interfaces\RecordInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RecordController extends Parser implements RecordInterface
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->prepare($args);
        $args = $this->editor($args);

        $template = filter('modal') !== null ? 'add' : 'editor';

        return $this->twig->render($response, 'old/'.$template.'.twig', $args);
    }

    public function content(array $args)
    {
        $args = $this->prepare($args);
        $args = $this->editor($args);

        $args['content'] = $args['editor_content'];

        return $args;
    }

    public function data($id_record)
    {
        $dbo = $database = $this->database;
        $defined_vars = get_defined_vars();

        // Lettura risultato query del modulo
        $init = $this->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $vars = get_defined_vars();

        $result = array_diff_key($vars, $defined_vars);
        unset($result['defined_vars']);
        unset($result['init']);

        return $result;
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $this->actions($args);

        return $response->withRedirect($args['module']->url('record', $args));
    }
}
