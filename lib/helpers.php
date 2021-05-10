<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
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

/*
 * Funzioni di aiuto per la semplificazione del codice.
 *
 * @since 2.4.2
 */
use HTMLBuilder\HTMLBuilder;
use Intl\Formatter;
use Models\Module;

/**
 * Restituisce l'oggetto dedicato alla gestione della connessione con il database.
 *
 * @return \Database
 */
function database()
{
    if (!app()->has(Database::class)) {
        app()->instance(Database::class, new Database());
    }

    return app()->get(Database::class);
}

/**
 * Prepara il parametro inserito per l'inserimento in una query SQL.
 * Attenzione: protezione di base contro SQL Injection.
 *
 * @param string $parameter
 *
 * @since 2.3
 *
 * @return mixed
 */
function prepare($parameter)
{
    return database()->prepare($parameter);
}

/**
 * Restituisce il contenuto sanitarizzato dell'input dell'utente.
 *
 * @param string $param  Nome del parametro
 * @param string $method Posizione del parametro (post o get)
 * @param bool   $raw    Restituire il valore non formattato
 *
 * @since 2.3
 *
 * @return string
 */
function filter($param, $method = null, $raw = false)
{
    return \Filter::getValue($param, $method, $raw);
}

/**
 * Restituisce il contenuto sanitarizzato dell'input dell'utente.
 *
 * @param string $param Nome del parametro
 * @param bool   $raw   Restituire il valore non formattato
 *
 * @since 2.3
 *
 * @return string
 */
function post($param, $raw = false)
{
    return \Filter::getValue($param, 'post', $raw);
}

/**
 * Restituisce il contenuto sanitarizzato dell'input dell'utente.
 *
 * @param string $param Nome del parametro
 * @param bool   $raw   Restituire il valore non formattato
 *
 * @since 2.3
 *
 * @return string
 */
function get($param, $raw = false)
{
    return \Filter::getValue($param, 'get', $raw);
}

/**
 * Legge il valore di un'impostazione dalla tabella zz_settings.
 *
 * @param string $name
 * @param bool   $again
 *
 * @since 2.4.2
 *
 * @return string
 */
function setting($name, $again = false)
{
    return \Models\Setting::pool($name)->valore;
}

/**
 * Restituisce l'oggetto dedicato alla gestione dei messaggi per l'utente.
 *
 * @since 2.4.2
 *
 * @return \Util\Messages
 */
function flash()
{
    return AppLegacy::flash();
}

/**
 * Restituisce l'oggetto dedicato alla gestione della conversione di numeri e date.
 *
 * @since 2.4.2
 *
 * @return \Intl\Formatter
 */
function formatter()
{
    if (!app()->has(Formatter::class)) {
        $formatter = new Formatter(
            app()->getLocale(),
            empty($options['timestamp']) ? 'd/m/Y H:i' : $options['timestamp'],
            empty($options['date']) ? 'd/m/Y' : $options['date'],
            empty($options['time']) ? 'H:i' : $options['time'],
            empty($options['number']) ? [
                'decimals' => ',',
                'thousands' => '.',
            ] : $options['number']
        );

        //$formatter->setPrecision(auth()->check() ? setting('Cifre decimali per importi') : 2);
        $formatter->setPrecision(2);

        app()->instance(Formatter::class, $formatter);
    }

    return app()->get(Formatter::class);
}

/**
 * Restituisce la traduzione del messaggio inserito.
 *
 * @param string $string
 * @param array  $parameters
 * @param string $operations
 *
 * @since 2.3
 *
 * @return string
 */
function tr($string, $parameters = [], $operations = [])
{
    $result = _i($string, $parameters);

    return replace($result, $parameters);
}

// Retro-compatibilità (con la funzione gettext)
if (!function_exists('_')) {
    function _($string, $parameters = [], $operations = [])
    {
        return tr($string, $parameters);
    }
}

/**
 * Restituisce il numero indicato formattato secondo la configurazione del sistema.
 *
 * @param float $number
 * @param int   $decimals
 *
 * @return string
 *
 * @since 2.4.8
 */
function numberFormat($number, $decimals = null)
{
    return formatter()->formatNumber($number, $decimals);
}

/**
 * Restituisce il timestamp indicato formattato secondo la configurazione del sistema.
 *
 * @param string $timestamp
+ *
 * @return string
 *
 * @since 2.4.8
 */
function timestampFormat($timestamp)
{
    return formatter()->formatTimestamp($timestamp);
}

/**
 * Restituisce la data indicata formattato secondo la configurazione del sistema.
 *
 * @param string $date
 *
 * @return string
 *
 * @since 2.4.8
 */
function dateFormat($date)
{
    return formatter()->formatDate($date);
}

/**
 * Restituisce l'orario indicato formattato secondo la configurazione del sistema.
 *
 * @param string $time
 *
 * @return string
 *
 * @since 2.4.8
 */
function timeFormat($time)
{
    return formatter()->formatTime($time);
}

/**
 * Restituisce il simbolo della valuta del gestione.
 *
 * @since 2.4.9
 *
 * @return string
 */
function currency()
{
    return AppLegacy::getCurrency();
}

/**
 * Restituisce il numero indicato formattato come una valuta secondo la configurazione del sistema.
 *
 * @param string $time
 *
 * @return string
 *
 * @since 2.4.9
 */
function moneyFormat($number, $decimals = null)
{
    return tr('_TOTAL_ _CURRENCY_', [
        '_TOTAL_' => numberFormat($number, $decimals),
        '_CURRENCY_' => currency(),
    ]);
}

/**
 * Restituisce il numero indicato formattato come una valuta secondo la configurazione del sistema.
 *
 * @return string
 *
 * @since 2.4.11
 */
function input(array $json)
{
    return HTMLBuilder::parse($json);
}

/*
 * Restituisce il modulo relativo all'identificativo.
 *
 * @since 2.5
 *
 * @return \Modules\Module
 */
function module($identifier)
{
    return Module::pool($identifier);
}
