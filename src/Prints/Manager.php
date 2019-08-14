<?php

namespace Prints;

use Controllers\Controller;
use Models\PrintTemplate;
use Psr\Container\ContainerInterface;

abstract class Manager extends Controller
{
    protected $print;
    protected $record_id;

    public function __construct(ContainerInterface $container, PrintTemplate $print, ?int $record_id = null){
        parent::__construct($container);

        $this->print = $print;
        $this->record_id = $record_id;
    }

    /**
     * Genera la stampa in PDF richiesta.
     */
    public abstract function render();

    /**
     * Salva la stampa in PDF richiesta.
     */
    public function save(string $directory){
        if (empty($directory) || !directory($directory)) {
            throw new \InvalidArgumentException();
        }
    }
}
