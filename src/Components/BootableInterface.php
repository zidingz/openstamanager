<?php

namespace Components;

/**
 * Interfaccia che definisce la modalità di interazione ufficiale del gestionale con gli oggetti che rappresentano i componenti a livello interno.
 *
 * @since 2.5
 */
interface BootableInterface
{
    public function getManager(): ComponentInterface;
}
