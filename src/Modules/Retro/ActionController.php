<?php

namespace Modules\Retro;

use Modules\Interfaces\ActionInterface;

class ActionController extends Parser implements ActionInterface
{
    public function __call($name, $arguments)
    {
        $action = $arguments[2]['action'];
        $action = str_replace(['-', '_'], [' ', ' '], $action);
        $action = lcfirst(ucwords($action));
        $action = str_replace(' ', '', $action);

        $op = filter('op');

        if (empty($op)) {
            $this->filter->set('get', 'op', $action);
            $this->filter->set('post', 'op', $action);
        }

        ob_start();
        $this->actions($arguments[2]);
        $result = ob_get_clean();

        $arguments[1]->write($result);

        return $arguments[1];
    }
}
