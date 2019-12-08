<?php

namespace Modules\Retro;

use Middlewares\Authorization\PermissionMiddleware;
use Middlewares\Authorization\UserMiddleware;
use Middlewares\ModuleMiddleware;
use Modules\Register as Original;
use Slim\App as SlimApp;
use Slim\Routing\RouteCollectorProxy;

class Register extends Original
{
    public function getUrl(string $name, array $parameters = [])
    {
        $prefix = $this->module->id.'-module';

        if (empty($parameters['record_id'])) {
            $name = $prefix;
        }else {
            $name = $prefix.'-record';
        }

        return urlFor($name, $parameters);
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

    public function updates(): array
    {
        return \Update::getUpdates(__DIR__.'/../update');
    }

    protected function routes(SlimApp $app): void
    {
        // Percorsi raggiungibili
        $prefix = $this->module->id.'-module';
        $app->group('/module-'.$this->module->id, function (RouteCollectorProxy $group) use ($prefix) {
            $group->get('/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':page')
                ->setName($prefix);

            $group->get('/add/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':add')
                ->setName($prefix.'-add');
            $group->post('/add/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':create')
                ->setName($prefix.'-add-save');

            $group->map(['GET', 'POST'], '/action/{action}/[reference/{reference_id:[0-9]+}/]', ActionController::class.':moduleAction')
                ->setName($prefix.'-action');

            $group->group('/edit/{record_id:[0-9]+}', function (RouteCollectorProxy $subgroup) use ($prefix) {
                $subgroup->get('/[reference/{reference_id:[0-9]+}/]', RecordController::class.':page')
                    ->setName($prefix.'-record');
                $subgroup->post('/[reference/{reference_id:[0-9]+}/]', RecordController::class.':update')
                    ->setName($prefix.'-record-save');

                $subgroup->map(['GET', 'POST'], '/action/{action}/[reference/{reference_id:[0-9]+}/]', ActionController::class.':recordAction')
                    ->setName($prefix.'-record-action');
            });
        })
            ->add(UserMiddleware::class)
            ->add(PermissionMiddleware::class)
            ->add(ModuleMiddleware::class);
    }
}
