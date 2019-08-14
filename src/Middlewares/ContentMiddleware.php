<?php

namespace Middlewares;

use Models\Module;
use Modules;
use Update;
use Util\Query;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ContentMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $route = $request->getAttribute('route');
        if (!$route || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $next($request, $response);
        }

        $this->addVariable('user', auth()->getUser());

        $this->addVariable('order_manager_id', $this->database->isInstalled() ? Modules::get('Stato dei serivizi')['id'] : null);
        $this->addVariable('is_mobile', isMobile());

        // Versione
        $this->addVariable('version', \Update::getVersion());
        $this->addVariable('revision', \Update::getRevision());

        // Richiesta AJAX
        $this->addVariable('handle_ajax', $request->isXhr() && filter('ajax'));

        // Menu principale
        $this->addVariable('main_menu', Modules::getMainMenu());

        return $next($request, $response);
    }
}
