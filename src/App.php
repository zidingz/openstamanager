<?php

/**
 * Classe per la gestione delle utenze.
 *
 * @since 2.4
 */
class App
{
    /** @var \Slim\Container */
    protected static $container = null;

    /** @var bool Stato di debug */
    protected static $config = [];

    /**
     * Restituisce la configurazione dell'installazione in utilizzo del progetto.
     *
     * @return array
     */
    public static function getConfig()
    {
        if (empty(self::$config['db_host'])) {
            if (file_exists(DOCROOT.'/config.inc.php')) {
                include DOCROOT.'/config.inc.php';

                $config = get_defined_vars();
            } else {
                $config = [];
            }

            $defaultConfig = self::getDefaultConfig();

            $result = array_merge($defaultConfig, $config);

            // Operazioni di normalizzazione sulla configurazione
            $result['debug'] = isset(self::$config['debug']) ? self::$config['debug'] : !empty($result['debug']);
            $result['lang'] = $result['lang'] == 'it' ? 'it_IT' : $result['lang'];

            self::$config = $result;
        }

        return self::$config;
    }

    /**
     * Imposta e restituisce lo stato di debug del progetto.
     *
     * @param bool $value
     *
     * @return bool
     */
    public static function debug($value = null)
    {
        if (is_bool($value)) {
            self::$config['debug'] = $value;
        }

        if (!isset(self::$config['debug'])) {
            App::getConfig();
        }

        return self::$config['debug'];
    }

    public static function setContainer($container)
    {
        self::$container = $container;
    }

    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * Restituisce il codice HTML per il form contenente il file indicato.
     *
     * @param string $file
     * @param array  $result
     * @param array  $options
     * @param bool   $disableForm
     *
     * @return string
     */
    public static function load($file, $result, $options, $disableForm = false)
    {
        $form = $disableForm ? '|response|' : self::internalLoad('form.php', $result, $options);

        $response = self::internalLoad($file, $result, $options);

        $form = str_replace('|response|', $response, $form);

        return $form;
    }

    /**
     * Restituisce il codice HTML generato del file indicato.
     *
     * @param string $file
     * @param array  $result
     * @param array  $options
     * @param string $directory
     *
     * @return string
     */
    public static function internalLoad($file, $result, $options, $directory = null)
    {
        $module = Modules::getCurrent();

        $database = $dbo = database();

        $id_module = $module['id'];
        $id_record = filter('id_record');

        $directory = empty($directory) ? 'resources\views|custom|/common/' : $directory;

        ob_start();
        include self::filepath($directory, $file);
        $response = ob_get_clean();

        return $response;
    }

    /**
     * Individua il percorso per il file da includere considerando gli eventuali custom.
     *
     * @param string $path
     * @param string $file
     *
     * @return string|null
     */
    public static function filepath($path, $file = null)
    {
        $path = str_contains($path, DOCROOT) ? $path : DOCROOT.'/'.ltrim($path, '/');
        $path = empty($file) ? $path : rtrim($path, '/').'/'.$file;

        $original_file = str_replace('|custom|', '', $path);
        $custom_file = str_replace('|custom|', '/custom', $path);

        $result = '';
        if (file_exists($custom_file)) {
            $result = $custom_file;
        } elseif (file_exists($original_file)) {
            $result = $original_file;
        }

        return slashes($result);
    }

    /**
     * Restituisce la configurazione di default del progetto.
     *
     * @return array
     */
    protected static function getDefaultConfig()
    {
        if (file_exists(DOCROOT.'/config.example.php')) {
            include DOCROOT.'/config.example.php';
        }

        $db_host = '';
        $db_username = '';
        $db_password = '';
        $db_name = '';
        $port = '';
        $lang = '';

        $formatter = [
            'timestamp' => 'd/m/Y H:i',
            'date' => 'd/m/Y',
            'time' => 'H:i',
            'number' => [
                'decimals' => ',',
                'thousands' => '.',
            ],
        ];

        return get_defined_vars();
    }
}
