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

    protected static $assets = null;

    protected static $config = [];

    /**
     * Restituisce la configurazione dell'installazione in utilizzo del progetto.
     *
     * @return array
     */
    public static function getConfig()
    {
        return self::getContainer()->get('config');
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
        return self::getContainer()->get('debug');
    }

    public static function setContainer($container)
    {
        self::$container = $container;
    }

    public static function getContainer()
    {
        return self::$container;
    }

    public static function asset(string $name)
    {
        if (!isset(self::$assets)) {
            $manifest = __DIR__.'/../public/assets/mix-manifest.json';
            $content = file_get_contents($manifest);

            self::$assets = (array) json_decode($content);
        }

        return '/assets'.self::$assets[$name];
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
        $module = \Modules\Module::getCurrent();

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
}
