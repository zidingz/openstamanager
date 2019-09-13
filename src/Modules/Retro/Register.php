<?php

namespace Modules\Retro;

use Middlewares\Authorization\PermissionMiddleware;
use Middlewares\Authorization\UserMiddleware;
use Middlewares\ModuleMiddleware;
use Models\Module;
use Modules\Register as Original;
use Slim\App as SlimApp;

class Register extends Original
{
    protected static $container;

    public static function boot(SlimApp $app, Module $module)
    {
        $container = $app->getContainer();
        self::$container = $container;

        // Inclusione modutil.php
        $file = $module->filepath('modutil.php');
        if (!empty($file)) {
            include_once $file;
        }

        // Inclusione Composer
        $file = $module->filepath('vendor/autoload.php');
        if (!empty($file)) {
            include_once $file;
        }

        // Supporto viste personalizzate
        $loader = $container['twig']->getLoader();
        $path = $module->filepath('views');
        $name = $module->directory;

        if (file_exists($path)) {
            $loader->addPath($path, $name);
        }

        // Percorsi raggiungibili
        $prefix = 'module-'.$module->id;
        $app->group('/'.$prefix, function () use ($app, $prefix) {
            $app->get('/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':page')
                ->setName($prefix);

            $app->get('/add/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':add')
                ->setName($prefix.'-add');
            $app->post('/add/[reference/{reference_id:[0-9]+}/]', ModuleController::class.':create')
                ->setName($prefix.'-add-save');

            $app->map(['GET', 'POST'], '/action/{action}/[reference/{reference_id:[0-9]+}/]', ActionManager::class.':moduleAction')
                ->setName($prefix.'-action');

            $app->group('/edit/{record_id:[0-9]+}', function () use ($app, $prefix) {
                $app->get('/[reference/{reference_id:[0-9]+}/]', RecordManager::class.':edit')
                    ->setName($prefix.'-record');
                $app->post('/[reference/{reference_id:[0-9]+}/]', RecordManager::class.':update')
                    ->setName($prefix.'-record-save');

                $app->map(['GET', 'POST'], '/action/{action}/[reference/{reference_id:[0-9]+}/]', ActionManager::class.':recordAction')
                    ->setName($prefix.'-record-action');
            });
        })
            ->add(UserMiddleware::class)
            ->add(PermissionMiddleware::class)
            ->add(ModuleMiddleware::class);
    }

    public static function getUrlName(Module $module, ?int $record_id)
    {
        $prefix = 'module-'.$module->id;

        if (empty($record_id)) {
            return $prefix;
        }

        return $prefix.'-record';
    }

    public static function getData(Module $module, ?int $id_record)
    {
        $dbo = $database = self::$container->database;
        $defined_vars = get_defined_vars();

        // Lettura risultato query del modulo
        $init = RetroController::filepath($module, 'init.php');
        if (!empty($init)) {
            include $init;
        }

        $vars = get_defined_vars();

        $result = array_diff_key($vars, $defined_vars);
        unset($result['defined_vars']);
        unset($result['init']);

        return $result;
    }
}
