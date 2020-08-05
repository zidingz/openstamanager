<?php

namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class RetroController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $require_path = realpath(DOCROOT.'/'.$args['path']);

        if (empty($args['path']) || $require_path == DOCROOT.'/index.php') {
            redirect(ROOTDIR);
            exit();
        }

        if (!starts_with($require_path, DOCROOT) || !file_exists($require_path) || !is_file($require_path)) {
            throw new HttpNotFoundException($request);
        }

        extract($args);

        $dbo = $database = $this->database;
        $id_module = $this->filter->getValue('id_module');
        $id_record = $this->filter->getValue('id_record');

        ob_start();
        require $require_path;
        $content = ob_get_clean();

        return $response->write($content);
    }
}
