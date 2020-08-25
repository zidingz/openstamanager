<?php

namespace Widgets;

/**
 * Tipologia di widget dedicato alla gestione di un modal aperto al click
 * Presenta un titolo e una valore personalizzato; al click produce l'apertura del modal specificato.
 *
 * @since 2.5
 */
abstract class ModalWidget extends Manager
{
    abstract public function getModal(): string;

    abstract public function getLink(): string;

    protected function getAttributes(): string
    {
        $title = $this->getTitle();

        return 'data-href="'.$this->getLink().'" data-toggle="modal" data-title="'.$title.'"';
    }
}
