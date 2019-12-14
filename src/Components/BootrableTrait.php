<?php

namespace Components;

/**
 * Implementazione di base dell'interazione con i componenti a livello interno.
 *
 * @since 2.5
 */
trait BootrableTrait
{
    protected $manager_object;

    public function getManager(): ComponentInterface
    {
        if (!isset($this->manager_object)) {
            $class = $this->attributes['class'];

            $this->manager_object = new $class($this);
        }

        return $this->manager_object;
    }
}
