<?php

namespace Middlewares;

use Slim\Exception\NotFoundException;
use Update;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Models\Module;
use Util\Query;

/**
 * Middleware per il blocco dei plugin senza riferimento al record genitore.
 *
 * @since 2.5
 */
class ModuleMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $route = $request->getAttribute('route');
        if (!$route || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $next($request, $response);
        }

        $args = $route->getArguments();

        Module::setCurrent($args['module_id']);
        Query::setModuleRecord($args['reference_id']);

        // Variabili fondamentali
        $module = Module::getCurrent();

        $args['id_module'] = $module['id'];
        $args['id_record'] = $args['record_id'];

        $args['structure'] = $module;
        $args['module'] = $module;

        // Argomenti di ricerca dalla sessione
        $this->addVariable('search', getSessionSearch($module['id']));

        // Gestione della visualizzazione plugin (reference_id obbligatorio)
        if (!empty($args['module']) && $args['module']->type != 'module' && !isset($args['reference_id'])) {
            throw new NotFoundException($request, $response);
        }

        // Impostazione degli argomenti
        $request = $this->setArgs($request, $args);

        return $next($request, $response);
    }
}

