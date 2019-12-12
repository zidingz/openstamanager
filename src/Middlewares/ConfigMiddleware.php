<?php

namespace Middlewares;

use Auth;
use Controllers\Config\ConfigurationController;
use Controllers\Config\InitController;
use Controllers\Config\RequirementsController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Response;
use Slim\Psr7\Stream;
use Update;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ConfigMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $config = $this->config;

        // Redirect al percorso HTTPS se impostato nella configurazione
        if (!empty($config['redirectHTTPS']) && !isHTTPS(true)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit();
        }

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

            $response = $this->response_factory->createResponse();
            return $response->withRedirect($this->router->urlFor($destination[0]));
        }

        return $handler->handle($request);
    }
}
