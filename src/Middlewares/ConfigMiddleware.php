<?php

namespace Middlewares;

use Auth;
use Controllers\Config\ConfigurationController;
use Controllers\Config\InitController;
use Controllers\Config\RequirementsController;
use Update;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ConfigMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        $original = $response;

        $route = $request->getAttribute('route');
        if (!$route) {
            return $next($request, $response);
        }

        // Requisiti di OpenSTAManager
        if (!RequirementsController::requirementsSatisfied()) {
            $destination = 'requirements';
        }

        // Inizializzazione
        elseif (!ConfigurationController::isConfigured()) {
            $destination = 'configuration';
        }

        // Installazione e/o aggiornamento
        elseif (Update::isUpdateAvailable()) {
            $destination = 'update';
        }

        // Configurazione informazioni di base
        elseif (!InitController::isInitialized()) {
            $destination = 'init';
        }

        if ($destination != $route->getName()) {
            Auth::logout();

            return $response->withRedirect($this->router->pathFor($destination));
        }

        return $next($request, $response);
    }
}
