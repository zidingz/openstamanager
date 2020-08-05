<?php

namespace Components;

use Slim\App as SlimApp;

/**
 * Classe per la gestione delle componenti indipendenti del gestionale.
 *
 * @since 2.5
 */
abstract class Component implements ComponentInterface
{
    protected static $container;
    protected $model;

    public function __construct(BootableInterface $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(SlimApp $app): void
    {
        $container = $app->getContainer();
        self::$container = $container;

        // Inclusione delle strutture PHP necessarie
        $this->autoload();

        // Caricamento dei template relativi
        $this->views();

        // Registrazione percorsi di navigazione
        $this->routes($app);
    }

    public function getContainer()
    {
        return self::$container;
    }

    /**
     * Gestisce l'inclusione delle componenti PHP necessarie al componente.
     */
    abstract protected function autoload(): void;

    /**
     * Gestisce l'inclusione delle componenti PHP necessarie al componente.
     */
    abstract protected function views(): void;

    /**
     * Gestisce la registrazione dei percorsi navigabili per il componente.
     */
    abstract protected function routes(SlimApp $app): void;
}
