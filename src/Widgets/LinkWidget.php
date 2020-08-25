<?php

namespace Widgets;

/**
 * Tipologia di widget indirizzato alla presentazione di un link ausiliario per l'utente finale.
 * Presenta esclusivamente un tiolo e al click prevede il reindirizzamento a un indirizzo specifico.
 *
 * @since 2.5
 */
abstract class LinkWidget extends Manager
{
    abstract public function getLink(): string;

    protected function getAttributes(): string
    {
        return 'href="'.$this->getLink().'"';
    }
}
