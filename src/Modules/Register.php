<?php

namespace Modules;

use Models\Module;
use Slim\App as SlimApp;

/**
 * Classe di base per la gestione della registrazione del modulo nell'applicazione.
 *
 * @since 2.5
 */
abstract class Register
{
    protected $module;
    protected static $container;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Inizializza il modulo all'interno dell'applicazione.
     *
     * @param SlimApp $app
     */
    public function boot(SlimApp $app)
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
     * Restituisce i contentuti HTML del modulo.
     * Utilizzato per il rendering dei plugin sottoposti ad un modulo.
     *
     * @param array $args
     *
     * @return mixed
     */
    abstract public function render(array $args = []);

    /**
     * Gestisce l'inclusione delle componenti PHP necessarie al modulo.
     */
    abstract protected function autoload(): void;

    /**
     * Gestisce l'inclusione delle componenti PHP necessarie al modulo.
     */
    abstract protected function views(): void;

    /**
     * Gestisce la registrazione dei percorsi navigabili per il modulo.
     *
     * @param SlimApp $app
     */
    abstract protected function routes(SlimApp $app): void;

    /**
     * Restutuisce un elenco di aggiornamenti presentati dal modulo.
     */
    abstract public function updates(): array;

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
