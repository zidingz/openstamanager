<?php

namespace Controllers\Retro;

use Managers\RecordInterface;
use Models\Module;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RecordController extends RetroController implements RecordInterface
{
    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->editor($args);

        return $this->twig->render($response, 'old/editor.twig', $args);
    }

    public function modal(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $args = $this->editor($args);

        $args['content'] = $args['editor_content'];

        return $this->twig->render($response, 'old/add.twig', $args);
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
        return $this->actions($args);
    }
}
