<?php

use Carbon\Carbon;

/**
 * Classe per gestire le traduzioni del progetto.
 *
 * @since 2.3
 */
class Translator
{
    /** @var Intl\Formatter Oggetto per la conversione di date e numeri nella lingua selezionata */
    protected static $formatter;
    /** @var string Simbolo della valuta corrente */
    protected static $currency;

    /** @var Symfony\Component\Translation\Translator Oggetto dedicato alle traduzioni */
    protected $translator;

    /** @var array Lingue disponibili */
    protected $locales = [];
    /** @var string Lingua selezionata */
    protected $locale;

    public function __construct($default_locale = 'it_IT', $fallback_locales = ['it_IT'])
    {
        $translator = new Symfony\Component\Translation\Translator($default_locale);
        $translator->setFallbackLocales($fallback_locales);
        // Imposta la classe per il caricamento
        $translator->addLoader('default', new Intl\FileLoader());

        $this->translator = $translator;

        $this->locale = $default_locale;
    }

    /**
     * Ricerca e aggiunge le traduzioni presenti nei percorsi predefiniti (cartella locale sia nella root che nei diversi moduli).
     *
     * @param string $string
     */
    public function addLocalePath($string)
    {
        $paths = glob($string);
        foreach ($paths as $path) {
            $this->addLocales($path);
        }
    }

    /**
     * Restituisce l'elenco dei linguaggi disponibili.
     *
     * @return array
     */
    public function getAvailableLocales()
    {
        return $this->locales;
    }

    /**
     * Controlla se il linguaggio indicato è disponibile.
     *
     * @param string $language
     *
     * @return bool
     */
    public function isLocaleAvailable($language)
    {
        return in_array($language, $this->getAvailableLocales());
    }

    /**
     * Imposta il linguaggio in utilizzo.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        if (!empty($locale) && $this->isLocaleAvailable($locale)) {
            $this->translator->setLocale($locale);
            $this->locale = $locale;

            setlocale(LC_TIME, $locale);
            Carbon::setLocale($locale);
        }
    }

    /**
     * Restituisce il linguaggio attualmente in utilizzo.
     *
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->locale;
    }

    /**
     * Restituisce l'oggetto responsabile della gestione delle traduzioni.
     *
     * @return Symfony\Component\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Restituisce la traduzione richiesta.
     *
     * @param string $string
     * @param array  $parameters
     * @param array  $operations
     *
     * @return string
     */
    public static function translate($string, $parameters = [], $operations = [])
    {
        $result = trans()->getTranslator()->trans($string, $parameters);

        // Operazioni aggiuntive sul risultato
        if (!empty($operations)) {
            $result = new Stringy\Stringy($result);

            if (!empty($operations['upper'])) {
                $result = $result->toUpperCase();
            } elseif (!empty($operations['lower'])) {
                $result = $result->toLowerCase();
            }
        }

        return (string) $result;
    }

    /**
     * Restituisce il simbolo della valuta del gestione.
     *
     * @since 2.4.9
     *
     * @return string
     */
    public static function getCurrency()
    {
        if (!isset(self::$currency)) {
            $id = setting('Valuta');
            $valuta = database()->fetchOne('SELECT symbol FROM zz_currencies WHERE id = '.prepare($id));

            self::$currency = $valuta['symbol'];
        }

        return self::$currency;
    }

    /**
     * Converte il numero dalla formattazione locale a quella inglese.
     *
     * @param string $string
     *
     * @return string
     */
    public static function numberToEnglish($string)
    {
        return formatter()->parseNumber($string);
    }

    /**
     * Converte il numero dalla formattazione inglese a quella locale.
     *
     * @param string     $string
     * @param string|int $decimals
     *
     * @return string
     */
    public static function numberToLocale($string, $decimals = null)
    {
        $string = !isset($string) ? 0 : $string;

        if (!empty($decimals) && is_string($decimals)) {
            $decimals = ($decimals == 'qta') ? setting('Cifre decimali per quantità') : null;
        }

        return formatter()->formatNumber($string, $decimals);
    }

    /**
     * Converte la data dalla formattazione locale a quella inglese.
     *
     * @param string $string
     *
     * @return string
     */
    public static function dateToEnglish($string)
    {
        return formatter()->parseDate($string);
    }

    /**
     * Converte la data dalla formattazione inglese a quella locale.
     *
     * @param string $string
     * @param string $fail
     *
     * @return string
     */
    public static function dateToLocale($string)
    {
        return formatter()->formatDate($string);
    }

    /**
     * Converte la data dalla formattazione locale a quella inglese.
     *
     * @param string $string
     *
     * @return string
     */
    public static function timeToEnglish($string)
    {
        return formatter()->parseTime($string);
    }

    /**
     * Converte la data dalla formattazione inglese a quella locale.
     *
     * @param string $string
     * @param string $fail
     *
     * @return string
     */
    public static function timeToLocale($string)
    {
        return formatter()->formatTime($string);
    }

    /**
     * Converte un timestamp dalla formattazione locale a quella inglese.
     *
     * @param string $timestamp
     *
     * @return string
     */
    public static function timestampToEnglish($string)
    {
        return formatter()->parseTimestamp($string);
    }

    /**
     * Converte un timestamp dalla formattazione inglese a quella locale.
     *
     * @param string $timestamp
     * @param string $fail
     *
     * @return string
     */
    public static function timestampToLocale($string)
    {
        return formatter()->formatTimestamp($string);
    }

    /**
     * Aggiunge i contenuti della cartella specificata alle traduzioni disponibili.
     *
     * @param string $path
     */
    protected function addLocales($path)
    {
        // Individua i linguaggi disponibili
        $dirs = glob($path.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->addLocale(basename($dir));
        }

        // Aggiunge le singole traduzioni
        foreach ($this->locales as $lang) {
            $done = [];

            $files = glob($path.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR.'*.*');
            foreach ($files as $file) {
                if (!in_array(basename($file), $done)) {
                    $this->translator->addResource('default', $file, $lang);

                    $done[] = basename($file);
                }
            }
        }
    }

    /**
     * Aggiunge il linguaggio indicato all'elenco di quelli disponibili.
     *
     * @param string $language
     */
    protected function addLocale($language)
    {
        if (!$this->isLocaleAvailable($language)) {
            $this->locales[] = $language;
        }
    }
}
