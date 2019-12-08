<?php

namespace Controllers;

use Auth;
use Controllers\Config\ConfigurationController;
use Controllers\Config\InitController;
use Controllers\Config\RequirementsController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Update;
use Models\Module;

class BaseController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        // Requisiti di OpenSTAManager
        if (!RequirementsController::requirementsSatisfied()) {
            Auth::logout();

            $controller = new ConfigurationController($this->container);
            $response = $controller->requirements($request, $response, $args);
        }

        // Inizializzazione
        elseif (!ConfigurationController::isConfigured()) {
            Auth::logout();

            $response = $response->withRedirect($this->router->pathFor('configuration'));
        }

        // Installazione e/o aggiornamento
        elseif (Update::isUpdateAvailable()) {
            Auth::logout();

            $response = $response->withRedirect($this->router->pathFor('update'));
        }

        // Configurazione informazioni di base
        elseif (!InitController::isInitialized()) {
            Auth::logout();

            $response = $response->withRedirect($this->router->pathFor('init'));
        }

        // Login
        elseif (!$this->auth->isAuthenticated()) {
            $args['has_backup'] = $this->database->isInstalled() && !Update::isUpdateAvailable() && setting('Backup automatico');
            $args['is_beta'] = Update::isBeta();
            $args['brute'] = [
                'actual' => Auth::isBrute(),
                'timeout' => Auth::getBruteTimeout(),
            ];

            $args['username'] = $this->flash->getFirstMessage('username');
            $args['keep_alive'] = $this->flash->getFirstMessage('keep_alive');

            $response = $this->twig->render($response, 'user\login.twig', $args);
        }

        // Redirect automatico al primo modulo disponibile
        else {
            $response = $this->redirectFirstModule($request, $response);
        }

        return $response;
    }

    public function loginAction(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $username = post('username');
        $password = post('password');
        $keep_alive = (filter('keep_alive') != null);

        if ($this->database->isConnected() && $this->database->isInstalled() && $this->auth->attempt($username, $password)) {
            $_SESSION['keep_alive'] = true;

            $response = $this->redirectFirstModule($request, $response);
        } else {
            $status = $this->auth->getCurrentStatus();

            flash()->error(Auth::getStatus()[$status]['message']);

            $this->flash->addMessage('username', $username);
            $this->flash->addMessage('keep_alive', $keep_alive);

            $response = $response->withRedirect($this->router->pathFor('login'));
        }

        return $response;
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        Auth::logout();

        $response = $response->withRedirect($this->router->pathFor('login'));

        return $response;
    }

    protected function redirectFirstModule($request, $response)
    {
        $module_id = $this->auth->getFirstModule();
        $module = Module::get($module_id);

        if (!empty($module)) {
            $response = $response->withRedirect($module->url('module'));
        } else {
            $response = $response->withRedirect($this->router->pathFor('logout'));
        }

        return $response;
    }
}