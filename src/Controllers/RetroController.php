<?php

namespace Controllers;

use Modules\Module;
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

        $content = $this->execute($require_path, $args);

        return $response->write($content);
    }

    protected function execute($require_path, $args)
    {
        extract($args);

        // Configurazione
        $config = $this->config;
        extract($config);

        $docroot = DOCROOT;
        $rootdir = ROOTDIR;

        // Moduli
        $dbo = $database = $this->database;
        $id_module = $this->filter->getValue('id_module');
        $id_record = $this->filter->getValue('id_record');
        $id_parent = $this->filter->getValue('id_parent');

        Module::setCurrent($id_module);
        $module = $structure = Module::getCurrent();
        $plugin = null;

        // Pagina diretta
        ob_start();
        require $require_path;
        $content = ob_get_clean();

        return $content;
    }
}
