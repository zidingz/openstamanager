<?php

namespace Modules\Retro;

use Middlewares\Authorization\PermissionMiddleware;
use Middlewares\Authorization\UserMiddleware;
use Middlewares\ModuleMiddleware;
use Modules\Register as Original;
use Slim\App as SlimApp;

class Register extends Original
{
    public function getUrlName(array $parameters = [])
    {
        $prefix = 'module-'.$this->module->id;

        if (empty($parameters['record_id'])) {
            return $prefix;
        }

        return $prefix.'-record';
    }

    public function getData(?int $id_record)
    {
        $dbo = $database = self::$container->database;
        $defined_vars = get_defined_vars();

        // Lettura risultato query del modulo
        $init = RetroController::filepath($this->module, 'init.php');
        if (!empty($init)) {
            include $init;
        }

        $vars = get_defined_vars();

        $result = array_diff_key($vars, $defined_vars);
        unset($result['defined_vars']);
        unset($result['init']);

        return $result;
    }

    public function render(array $args = [])
    {
        $controller = new ModuleController(self::$container);

        $args['module_id'] = $this->module->id;
        $args['id_module'] = $this->module->id;
        $args['module'] = $this->module;
        $args['structure'] = $this->module;

        $result = $controller->content($args);

        return $result;
    }

    protected function autoload(): void
    {
        // Inclusione modutil.php
        $file = $this->module->filepath('modutil.php');
        if (!empty($file)) {
            include_once $file;
        }

        // Inclusione Composer
        $file = $this->module->filepath('vendor/autoload.php');
        if (!empty($file)) {
            include_once $file;
        }
    }

    protected function views(): void
    {
        $path = $this->module->filepath('views');
        $name = $this->module->directory;

        $this->addView($path, $name);
    }

    protected function updates(): array
    {
        return \Update::getUpdates(__DIR__.'/../update');
    }

    protected function routes(SlimApp $app): void
    {
        // Percorsi raggiungibili
        $prefix = 'module-'.$this->module->id;
        $app->group('/'.$prefix, function () use ($app, $prefix) {
            $app->get('/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':page')
                ->setName($prefix);

            $app->get('/add/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':add')
                ->setName($prefix.'-add');
            $app->post('/add/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':create')
                ->setName($prefix.'-add-save');

            $app->map(['GET', 'POST'], '/action/{action}/[reference/{reference_id:[0-9]+}/]', ActionController::class.':moduleAction')
                ->setName($prefix.'-action');

            $app->group('/edit/{record_id:[0-9]+}', function () use ($app, $prefix) {
                $app->get('/[reference/{reference_id:[0-9]+}/]', RecordController::class.':page')
                    ->setName($prefix.'-record');
                $app->post('/[reference/{reference_id:[0-9]+}/]', RecordController::class.':update')
                    ->setName($prefix.'-record-save');

                $app->map(['GET', 'POST'], '/action/{action}/[reference/{reference_id:[0-9]+}/]', ActionController::class.':recordAction')
                    ->setName($prefix.'-record-action');
            });
        })
            ->add(UserMiddleware::class)
            ->add(PermissionMiddleware::class)
            ->add(ModuleMiddleware::class);
    }
}
