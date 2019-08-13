<?php

namespace Controllers\Retro;

use Managers\RecordInterface;
use Models\Module;

class RecordController extends RetroController implements RecordInterface
{
    public function page($request, $response, $args)
    {
        $response = $this->editor($request, $response, $args);

        return $response;
    }

    public function dialog($request, $response, $args)
    {
        $response = $this->controller($request, $response, $args);

        return $response;
    }

    public function data($id_record)
    {
        $dbo = $database = $this->database;

        $class = get_class($this);
        $pieces = explode('\\', $class, -1);
        $namespace = implode('\\', $pieces);

        $module = Module::where('namespace', $namespace)->first();
        $defined_vars = get_defined_vars();

        // Lettura risultato query del modulo
        $init = $module->filepath('init.php');
        if (!empty($init)) {
            include $init;
        }

        $vars = get_defined_vars();

        $result = array_diff_key($vars, $defined_vars);
        unset($result['defined_vars']);
        unset($result['init']);

        return $result;
    }

    public function update($request, $response, $args)
    {
        return $this->actions($request, $response, $args);
    }
}
