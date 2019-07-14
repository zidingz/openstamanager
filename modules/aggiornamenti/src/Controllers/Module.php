<?php

namespace Modules\Aggiornamenti\Controllers;

use Controllers\Config\RequirementsController as Requirements;
use InvalidArgumentException;
use Managers\ModuleManager;
use Modules\Aggiornamenti\Aggiornamento;
use Modules\Aggiornamenti\DowngradeException;

class Module extends ModuleManager
{
    public function page($request, $response, $args)
    {
        $args['action_link'] = pathFor('module-action', [
            'module_id' => $args['module_id'],
            'action' => '|action|',
        ]);

        try {
            $args['update'] = new Aggiornamento();

            $args['update_version'] = $args['update']->getVersion();
            $args['update_requirements'] = $args['update']->getRequirements();

            $response = $this->twig->render($response, '@aggiornamenti\update.twig', $args);

            return $response;
        } catch (InvalidArgumentException $e) {
        }

        $custom = custom();
        $tables = customTables();

        // Aggiornamenti
        $alerts = [];

        if (!extension_loaded('zip')) {
            $alerts[tr('Estensione ZIP')] = tr('da abilitare');
        }

        $upload_max_filesize = ini_get('upload_max_filesize');
        $upload_max_filesize = str_replace(['k', 'M'], ['000', '000000'], $upload_max_filesize);
        // Dimensione minima: 32MB
        if ($upload_max_filesize < 32000000) {
            $alerts['upload_max_filesize'] = '32MB';
        }

        $post_max_size = ini_get('post_max_size');
        $post_max_size = str_replace(['k', 'M'], ['000', '000000'], $post_max_size);
        // Dimensione minima: 32MB
        if ($post_max_size < 32000000) {
            $alerts['post_max_size'] = '32MB';
        }

        $args['custom'] = $custom;
        $args['tables'] = $tables;
        $args['alerts'] = $alerts;
        $args['enable_updates'] = setting('Attiva aggiornamenti');

        $args['requirements'] = Requirements::getRequirementsList();

        $response = $this->twig->render($response, '@aggiornamenti\module.twig', $args);

        return $response;
    }

    public function add($request, $response, $args)
    {
    }

    public function create($request, $response, $args)
    {
        if (!setting('Attiva aggiornamenti')) {
            die(tr('Accesso negato'));
        }

        try {
            $update = Aggiornamento::make($_FILES['blob']['tmp_name']);
        } catch (DowngradeException $e) {
            flash()->error(tr('Il pacchetto contiene una versione precedente del gestionale'));
        } catch (InvalidArgumentException $e) {
            flash()->error(tr('Il pacchetto contiene solo componenti già installate e aggiornate'));
        }
    }

    public function check($request, $response, $args)
    {
        $result = Aggiornamento::isAvailable();
        $result = $result === false ? 'none' : $result;

        $response->write($result);

        return $response;
    }

    public function download($request, $response, $args)
    {
        Aggiornamento::download();

        return $response;
    }

    public function execute($request, $response, $args)
    {
        try {
            $update = new Aggiornamento();

            $update->execute();
        } catch (InvalidArgumentException $e) {
        }

        $route = $this->router->pathFor('module', [
            'module_id' => $args['module_id'],
        ]);
        $response = $response->withRedirect($route);

        return $response;
    }

    public function cancel($request, $response, $args)
    {
        try {
            $update = new Aggiornamento();

            $update->delete();
        } catch (InvalidArgumentException $e) {
        }

        $route = $this->router->pathFor('module', [
            'module_id' => $args['module_id'],
        ]);
        $response = $response->withRedirect($route);

        return $response;
    }
}
