<?php

namespace Traits;

trait StoreTrait
{
    /** @var Illuminate\Support\Collection Collezione degli oggetti disponibili */
    protected static $collection = null;
    /** @var bool Controllo sul salvataggio globale */
    protected static $all = false;

    /** @var int Identificatore dell'oggetto in utilizzo */
    protected static $current;

    /**
     * Restituisce tutti gli oggetti.
     *
     * @return Illuminate\Support\Collection
     */
    public static function getAll()
    {
        if (!self::$all) {
            self::$collection = self::all();

            self::$all = true;
        }

        return self::$collection;
    }

    /**
     * Nome della colonna "id" (Primary Key).
     *
     * @return string
     */
    public static function getStoreIdentifier()
    {
        return 'id';
    }

    /**
     * Nome della colonna "name".
     *
     * @return string
     */
    public static function getStoreNameIdentifier()
    {
        return 'name';
    }

    /**
     * Restituisce l'oggetto relativo all'identificativo specificato.
     *
     * @param string|int $identifier
     *
     * @return StoreTrait
     */
    public static function get($identifier)
    {
        if (empty($identifier)) {
            return null;
        }

        $name_field = self::getStoreNameIdentifier();
        $id_field = self::getStoreIdentifier();

        // Inizializzazione
        if (!isset(self::$collection)) {
            self::$collection = collect();
        }

        // Ricerca
        $result = self::$collection->first(function ($item) use ($identifier, $id_field, $name_field) {
            return $item->{$id_field} == $identifier || $item->{$name_field} == $identifier;
        });

        if (!empty($result)) {
            return $result;
        }

        // Consultazione Database
        $result = self::where($id_field, $identifier)
            ->orWhere($name_field, $identifier)
            ->first();

        if (!empty($result)) {
            self::$collection->push($result);
        }

        return $result;
    }

    /**
     * Restituisce l'oggetto attualmente impostato.
     *
     * @return StoreTrait
     */
    public static function getCurrent()
    {
        if (!isset(self::$current)) {
            return null;
        }

        return self::get(self::$current);
    }

    /**
     * Imposta il modulo attualmente in utilizzo.
     *
     * @param int $id
     */
    public static function setCurrent($id)
    {
        self::$current = $id;
    }
}
