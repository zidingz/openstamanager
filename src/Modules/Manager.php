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

namespace Modules;

use Components\Component;

/**
 * Classe di base per la gestione della registrazione del modulo nell'applicazione.
 *
 * @since 2.5
 */
abstract class Manager extends Component
{
    protected $module;

    public function __construct(Module $module)
    {
        parent::__construct($module);

        $this->module = $module;
    }

    /**
     * Restituisce il nome relativo ad un'azione specificata dai parametri.
     * Utilizzato per comporre correttamente gli indirizzi nelle parti autonome di indirizzamento del gestionale.
     *
     * @return mixed
     */
    abstract public function getUrl(string $name, array $parameters = []);

    /**
     * Restituisce le informazioni disponibili per il modulo in relazione a un determinato record.
     * Utilizzato per il completamento delle informazioni all'interno dei plugin.
     *
     * @return mixed
     */
    abstract public function getData(?int $id_record);

    /**
     * Registra un nuovo namespace Twig per l'applicazione.
     */
    protected function addView(string $path, string $name): void
    {
        $loader = self::$container->get('twig')->getLoader();

        if (file_exists($path)) {
            $loader->addPath($path, $name);
        }
    }
}
