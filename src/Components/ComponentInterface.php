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
     */
    public function boot(SlimApp $app): void;

    /**
     * Restituisce i contenuti HTML del componente.
     */
    public function render(array $args = []): string;

    /**
     * Restituisce un elenco di aggiornamenti presentati dal componente.
     */
    public function updates(): array;
}
