<?php

namespace Modules;

use Models\Module;
use Slim\App;

abstract class Register
{
    abstract public static function boot(App $app, Module $module);

    abstract public static function getUrlName(Module $module, ?int $record_id);

    abstract public static function getData(Module $module, ?int $id_record);
}
