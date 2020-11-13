<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.n.c.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

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
