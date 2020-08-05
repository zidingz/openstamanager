<?php

namespace Modules\Retro;

use Middlewares\Authorization\PermissionMiddleware;
use Middlewares\Authorization\UserMiddleware;
use Middlewares\RetroModuleMiddleware;
use Modules\Manager as Original;
use Modules\Retro\Controllers\ModuleController;
use Modules\Retro\Controllers\RecordController;
use Slim\App as SlimApp;
use Slim\Routing\RouteCollectorProxy;

class Manager extends Original
{
    public function getUrl(string $name, array $parameters = [])
    {
        $prefix = $this->module->id.'-module';

        if ($name == 'module') {
            $name = $prefix;
        } elseif ($name == 'record' && !empty($parameters['record_id'])) {
            $name = $prefix.'-record';
        } elseif ($name == 'add') {
            $name = $prefix.'-add';
        }

        return urlFor($name, $parameters);
    }

    public function getData(?int $id_record)
    {
        $dbo = $database = self::$container->get('database');
        $defined_vars = get_defined_vars();

        // Lettura risultato query del modulo
        $init = Parser::filepath($this->module, 'init.php');
        if (!empty($init)) {
            include $init;
        }

        $vars = get_defined_vars();

        $result = array_diff_key($vars, $defined_vars);
        unset($result['defined_vars']);
        unset($result['init']);

        return $result;
    }

    public function render(array $args = []): string
    {
        $controller = new ModuleController(self::$container);

        $args['module_id'] = $this->module->id;
        $args['id_module'] = $this->module->id;
        $args['module'] = $this->module;
        $args['structure'] = $this->module;

        $result = $controller->content($args);

        return $result ?: '';
    }

    public function updates(): array
    {
        return \Update::getUpdates(__DIR__.'/../update');
    }

    protected function autoload(): void
    {
        // Inclusione modutil.php
        $file = Parser::filepath($this->module, 'modutil.php');
        if (!empty($file)) {
            include_once $file;
        }

        // Inclusione Composer
        $file = Parser::filepath($this->module, 'vendor/autoload.php');
        if (!empty($file)) {
            include_once $file;
        }
    }

    protected function views(): void
    {
        $path = Parser::filepath($this->module, 'views');
        $name = $this->module->directory;

        $this->addView($path, $name);
    }

    protected function routes(SlimApp $app): void
    {
        $prefix = $this->module->id.'-module';

        $reference_suffix = '';
        if ($this->module->type == 'record_plugin') {
            $reference_suffix = '[reference/{reference_id:[0-9]+}/]';
        }

        // Percorsi raggiungibili
        $app->group('/module-'.$this->module->id, function (RouteCollectorProxy $group) use ($prefix, $reference_suffix) {
            $group->get('/'.$reference_suffix, ModuleController::class.':page')
                ->setName($prefix);

            $group->get('/add/'.$reference_suffix, ModuleController::class.':add')
                ->setName($prefix.'-add');
            $group->post('/add/'.$reference_suffix, ModuleController::class.':create')
                ->setName($prefix.'-add-save');

            $group->map(['GET', 'POST'], '/action/{action}/'.$reference_suffix, ActionController::class.':moduleAction')
                ->setName($prefix.'-action');

            $group->group('/edit/{record_id:[0-9]+}', function (RouteCollectorProxy $subgroup) use ($prefix, $reference_suffix) {
                $subgroup->get('/'.$reference_suffix, RecordController::class.':page')
                    ->setName($prefix.'-record');
                $subgroup->post('/'.$reference_suffix, RecordController::class.':update')
                    ->setName($prefix.'-record-save');

                $subgroup->map(['GET', 'POST'], '/action/{action}/'.$reference_suffix, ActionController::class.':recordAction')
                    ->setName($prefix.'-record-action');
            });
        })
            ->add(UserMiddleware::class)
            ->add(PermissionMiddleware::class)
            ->add(RetroModuleMiddleware::class);
    }
}
