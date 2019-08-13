<?php

namespace Controllers\Retro;

use Managers\ActionInterface;

class ActionManager extends RetroController implements ActionInterface
{
    public function __call($name, $arguments)
    {
        $action = $arguments[2]['action'];
        $op = filter('op');

        if (empty($op)) {
            $this->filter->set('get', 'op', $action);
            $this->filter->set('post', 'op', $action);
        }

        ob_start();
        $this->actions($arguments[0], $arguments[1], $arguments[2]);
        $result = ob_get_clean();

        $arguments[1]->write($result);

        return $arguments[1];
    }
}
