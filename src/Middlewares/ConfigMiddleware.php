<?php

namespace Middlewares;

use Auth;
use Controllers\Config\ConfigurationController;
use Controllers\Config\InitController;
use Controllers\Config\RequirementsController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Update;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ConfigMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $route = $this->getRoute($request);
        if (empty($route)) {
            return $handler->handle($request);
        }

        $destination = [];

        // Requisiti di OpenSTAManager
        if (!RequirementsController::requirementsSatisfied()) {
            $destination = ['requirements'];
        }

        // Inizializzazione
        elseif (!ConfigurationController::isConfigured()) {
            $destination = ['configuration', 'configuration-save', 'configuration-test'];
        }

        // Installazione e/o aggiornamento
        elseif (Update::isUpdateAvailable()) {
            $destination = ['update', 'update-progress'];
        }

        // Configurazione informazioni di base
        elseif (!InitController::isInitialized()) {
            $destination = ['init', 'init-save'];
        }

        if (!empty($destination) && !in_array($route->getName(), $destination)) {
            Auth::logout();

            return $response->withRedirect($this->router->urlFor($destination[0]));
        }

        return $handler->handle($request);
    }
}
