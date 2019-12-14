<?php

namespace Components;

use Slim\App as SlimApp;

/**
 * Interfaccia che definisce la modalità di interazione ufficiale del gestionale con le varie componenti indipendenti.
 *
 * @since 2.5
 */
interface ComponentInterface
{
    /**
     * Inizializza il componente all'interno dell'applicazione.
     *
     * @param SlimApp $app
     */
    public function boot(SlimApp $app): void;

    /**
     * Restituisce i contentuti HTML del componente.
     *
     * @param array $args
     *
     * @return string
     */
    public function render(array $args = []): string;

    /**
     * Restutuisce un elenco di aggiornamenti presentati dal componente.
     *
     * @return array
     */
    public function updates(): array;
}
