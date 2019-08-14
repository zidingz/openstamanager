<?php

namespace Modules;

use Controllers\Controller;
use Controllers\ModuleController;
use Models\Module;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Manager extends Controller
{
    protected $module;
    protected $record_id;
    protected $reference_id;

    public function __construct(ContainerInterface $container, Module $module, ?int $record_id = null, ?int $reference_id = null)
    {
        parent::__construct($container);

        $this->module = $module;
        $this->record_id = $record_id;
        $this->reference_id = $reference_id;
    }

    /**
     * Completamento delle informazioni per il rendering del modulo.
     *
     * @param array $args
     * @return array
     */
    protected function prepare(array $args)
    {
        $args['module'] = $this->module;
        $args['structure'] = $this->module;
        $args['module_id'] = $this->module->id;
        $args['id_module'] = $this->module->id;

        $args['record_id'] = $this->record_id;
        $args['id_record'] = $this->record_id;

        $args['record'] = null;
        $args['reference_id'] = $this->reference_id;
        $args['reference_record'] = $this->getReferenceRecord($args);

        return $args;
    }

    abstract public function getReferenceID(array $args);

    public function getReferenceRecord($args)
    {
        $module = $args['structure'];
        if ($module->type == 'module') {
            return null;
        }

        //$class = ModuleController::getControllerClass($module->parent()->first(), 'Record');
        $id_record = $this->getReferenceID($args);
/*
        if (!empty($id_record) && !empty($class)) {
            $manager = new $class($this->container);
            $data = $manager->data($id_record);

            $result = $data['record'];
        }
*/
        return $result;
    }

    public function plugins(array $args){
        $plugins = $args['plugins'];
        $args['plugins'] = [];

        $results = [];
        foreach ($plugins as $plugin) {
            $controller = $plugin->getController($this->container, 'Module', null, $this->record_id);

            if (!empty($controller)){
                $results[$plugin->id] = $controller->content($args);
            }
        }

        return $results;
    }
}
