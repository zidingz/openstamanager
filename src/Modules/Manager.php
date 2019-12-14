<?php

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
        $this->module = $module;
    }

    /**
     * Restituisce il nome relativo ad un'azione specificata dai parametri.
     * Utilizzato per comporre correttamente gli indirizzi nelle parti autonome di indirizzamento del gestione.
     *
     * @param array $parameters
     *
     * @return mixed
     */
    abstract public function getUrl(string $name, array $parameters = []);

    /**
     * Restituisce le informazioni disponibili al modulo a riguardo di un determinato record.
     * Utilizzato per il completamento delle informazioni all'interno dei plugin.
     *
     * @param int|null $id_record
     *
     * @return mixed
     */
    abstract public function getData(?int $id_record);

    /**
     * Registra un nuovo namespace Twig per l'applicazione.
     *
     * @param string $path
     * @param string $name
     */
    protected function addView(string $path, string $name)
    {
        $loader = self::$container->get('twig')->getLoader();

        if (file_exists($path)) {
            $loader->addPath($path, $name);
        }
    }
}
