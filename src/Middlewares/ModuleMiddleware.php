<?php

namespace Middlewares;

use Models\Module;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\NotFoundException;
use Update;
use Util\Query;

/**
 * Middleware per il blocco dei plugin senza riferimento al record genitore.
 *
 * @since 2.5
 */
class ModuleMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $route = $this->getRoute($request);
        if (empty($route) || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $handler->handle($request);
        }

        $name = $route->getName();
        $module_id = explode('-', $name)[0];

        $args = $route->getArguments();
        $args['module_id'] = $args['module_id'] ?: $module_id;

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
        if (!empty($args['module']) && $args['module']->type == 'record_plugin' && !isset($args['reference_id'])) {
            throw new NotFoundException($request, $response);
        }

        // Impostazione degli argomenti
        $request = $this->setArgs($request, $args);

        return $handler->handle($request);
    }
}
