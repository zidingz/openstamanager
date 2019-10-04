<?php

namespace Modules;

use Middlewares\Authorization\PermissionMiddleware;
use Middlewares\Authorization\UserMiddleware;
use Middlewares\ModuleMiddleware;
use Models\Module;
use Modules\Retro\ActionManager;
use Modules\Retro\ModuleController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App as SlimApp;
use Util\Query;

abstract class Register
{
    protected $module;
    protected static $container;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function boot(SlimApp $app){
        $container = $app->getContainer();
        self::$container = $container;
    }

    abstract public function getUrlName(array $parameters = []);

    abstract public function getData(?int $id_record);

    abstract public function render(array $args = []);
}
