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
