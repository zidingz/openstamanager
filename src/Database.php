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

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Classe per gestire la connessione al database.
 *
 * @since 2.3
 */
class Database extends Util\Singleton
{
    /** @var \Illuminate\Database\Capsule\Manager Gestore di connessione Laravel */
    protected $capsule;

    /** @var string Nome del database */
    protected $database_name;

    /** @var bool Stato di connessione del database */
    protected $is_connected;
    /** @var bool Stato di installazione del database */
    protected $is_installed;

    /** @var string Versione corrente di MySQL */
    protected $mysql_version;

    /**
     * Costruisce la nuova connessione al database.
     * Ispirato dal framework open-source Medoo.
     *
     * @param string|array $server
     * @param string       $username
     * @param string       $password
     * @param string       $database_name
     * @param string       $charset
     *
     * @since 2.3
     *
     */
    protected function __construct($server, $username, $password, $database_name, $charset = null)
    {
    }

    /**
     * Restituisce la connessione attiva al database, creandola nel caso non esista.
     *
     * @since 2.3
     *
     * @return Database
     */
    public static function getConnection($new = false, $data = [])
    {
        $class = get_called_class();

        if (empty(parent::$instance[$class]) || !parent::$instance[$class]->isConnected() || $new) {
            $config = AppLegacy::getConfig();

            // Sostituzione degli eventuali valori aggiuntivi
            $config = array_merge($config, $data);

            parent::$instance[$class] = new self($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);
        }

        return parent::$instance[$class];
    }

    public static function getInstance()
    {
        return self::getConnection();
    }

    /**
     * Restituisce l'oggetto PDO artefice della connessione.
     *
     * @since 2.3
     *
     * @return \DebugBar\DataCollector\PDO\TraceablePDO|PDO
     */
    public function getPDO()
    {
        return DB::connection()->getPDO();
    }


    /**
     * Controlla se la connessione è valida e andata a buon fine.
     *
     * @since 2.3
     *
     * @return bool
     */
    public function isConnected()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
           return false;
        }
    }

    /**
     * Controlla se il database necessario al progetto esiste.
     *
     * @since 2.3
     *
     * @return bool
     */
    public function isInstalled()
    {
        if (empty($this->is_installed)) {
            $this->is_installed = $this->tableExists('zz_modules');
        }

        return $this->is_installed;
    }

    /**
     * Restituisce la versione del DBMS MySQL in utilizzo.
     *
     * @since 2.3
     *
     * @return string
     */
    public function getMySQLVersion()
    {
        if (empty($this->mysql_version) && $this->isConnected()) {
            $ver = $this->fetchArray('SELECT VERSION()');
            if (!empty($ver[0]['VERSION()'])) {
                $this->mysql_version = explode('-', $ver[0]['VERSION()'])[0];
            }
        }

        return $this->mysql_version;
    }

    /**
     * Restituisce il nome del database a cui si è connessi.
     *
     * @since 2.3
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database_name;
    }

    /**
     * Esegue la query indicata, restituendo l'identificativo della nuova entità se si tratta di una query di inserimento.
     *
     * @since 2.0
     *
     * @param string $query      Query da eseguire
     * @param array  $parameters
     *
     * @return int
     */
    public function query($query, $parameters = [])
    {
        $statement = $this->getPDO()->prepare($query);
        $statement->execute($parameters);

        $id = $this->lastInsertedID();
        if ($id == 0) {
            return 1;
        } else {
            return $id;
        }
    }

    /**
     * Restituisce un'array strutturato in base ai nomi degli attributi della selezione.
     *
     * @param string $query      Query da eseguire
     * @param array  $parameters
     * @param bool   $numeric
     *
     * @throws Exception
     *
     * @return array
     */
    public function fetchArray($query, $parameters = [], $numeric = false)
    {
        $mode = empty($numeric) ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;

        $statement = $this->getPDO()->prepare($query);
        $statement->execute($parameters);

        $result = $statement->fetchAll($mode);

        return $result;
    }

    /**
     * Restituisce un'array strutturato in base agli indici degli attributi della selezione.
     *
     * @since 2.0
     * @deprecated 2.3
     *
     * @param string $query Query da eseguire
     *
     * @return array
     */
    public function fetchRows($query)
    {
        return $this->fetchArray($query, [], true);
    }

    /**
     * Restituisce il primo elemento della selezione, strutturato in base ai nomi degli attributi.
     *
     * @since 2.0
     * @deprecated 2.3
     *
     * @param string $query Query da eseguire
     *
     * @return array
     */
    public function fetchRow($query)
    {
        return $this->fetchOne($query);
    }

    /**
     * Restituisce il primo elemento della selezione, strutturato in base ai nomi degli attributi.
     * Attenzione: aggiunge il LIMIT relativo a fine della query.
     *
     * @since 2.4
     *
     * @param string $query Query da eseguire
     *
     * @return array
     */
    public function fetchOne($query, $parameters = [])
    {
        if (!string_contains($query, 'LIMIT')) {
            $query .= ' LIMIT 1';
        }

        $result = $this->fetchArray($query, $parameters);

        if (isset($result[0])) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Restituisce il numero dei risultati della selezione.
     *
     * @since 2.0
     *
     * @param string $query Query da eseguire
     *
     * @return int
     */
    public function fetchNum($query, $parameters = [])
    {
        $result = $this->fetchArray('SELECT COUNT(*) as `tot` FROM ('.$query.') AS `count`', $parameters);

        if (!empty($result)) {
            return $result[0]['tot'];
        }

        return 0;
    }

    public function tableExists($table)
    {
        if ($this->isConnected()) {
            return Schema::hasTable($table);
        }

        return null;
    }

    /**
     * Restituisce l'identificativo dell'ultimo elemento inserito.
     *
     * @since 2.0
     * @deprecated 2.3
     *
     * @return int
     */
    public function last_inserted_id()
    {
        return $this->lastInsertedID();
    }

    /**
     * Restituisce l'identificativo dell'ultimo elemento inserito.
     *
     * @since 2.3
     *
     * @return int
     */
    public function lastInsertedID()
    {
        try {
            return $this->getPDO()->lastInsertId();
        } catch (PDOException $e) {
            throw new PDOException(tr("Impossibile ottenere l'ultimo identificativo creato"));
        }
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
    public function prepare($parameter)
    {
        return $this->getPDO()->quote($parameter);
    }

    /**
     * Costruisce la query per l'INSERT definito dagli argomenti.
     *
     * @since 2.3
     *
     * @param string $table
     * @param array  $array
     *
     * @return string|array
     */
    public function insert($table, $array)
    {
        if (!is_string($table) || !is_array($array)) {
            throw new UnexpectedValueException();
        }

        if (!isset($array[0]) || !is_array($array[0])) {
            $array = [$array];
        }

        return DB::table($table)->insert($array);
    }

    /**
     * Costruisce la query per l'UPDATE definito dagli argomenti.
     *
     * @since 2.3
     *
     * @param string $table
     * @param array  $array
     * @param array  $conditions
     *
     * @return string|array
     */
    public function update($table, $array, $conditions)
    {
        if (!is_string($table) || !is_array($array) || !is_array($conditions)) {
            throw new UnexpectedValueException();
        }

        return DB::table($table)->where($conditions)->update($array);
    }

    /**
     * Costruisce la query per il SELECT definito dagli argomenti.
     *
     * @since 2.3
     *
     * @param string       $table
     * @param array        $array
     * @param array        $conditions
     * @param array        $order
     * @param string|array $limit
     * @param bool         $return
     *
     * @return string|array
     */
    public function select($table, $array = [], $conditions = [], $order = [], $limit = null, $return = false)
    {
        if (
            !is_string($table) ||
            (!empty($order) && !is_string($order) && !is_array($order)) ||
            (!empty($limit) && !is_string($limit) && !is_integer($limit) && !is_array($limit))
        ) {
            throw new UnexpectedValueException();
        }

        // Valori da ottenere
        $select = [];
        foreach ((array) $array as $key => $value) {
            $select[] = $value.(is_numeric($key) ? '' : ' AS '.$key);
        }
        $select = !empty($select) ? $select : ['*'];

        $statement = DB::table($table)->where($conditions)->select($select);

        // Impostazioni di ordinamento
        if (!empty($order)) {
            foreach ((array) $order as $key => $value) {
                $order = is_numeric($key) ? 'ASC' : strtoupper($value);
                $field = is_numeric($key) ? $value : $key;

                if ($order == 'ASC') {
                    $statement = $statement->orderBy($field);
                } else {
                    $statement = $statement->orderBy($field, 'DESC');
                }
            }
        }

        // Eventuali limiti
        if (!empty($limit)) {
            $offset = is_array($limit) ? $limit[0] : null;
            $count = is_array($limit) ? $limit[1] : $limit;

            if ($offset) {
                $statement = $statement->offset($offset);
            }

            $statement = $statement->limit($count);
        }

        if (!empty($return)) {
            return $statement->toSql();
        } else {
            $result = $statement->get()->toArray();

            return json_decode(json_encode($result), true);
        }
    }

    /**
     * Costruisce la query per il SELECT definito dagli argomenti (LIMIT 1).
     *
     * @since 2.4.1
     *
     * @param string $table
     * @param array  $array
     * @param array  $conditions
     * @param array  $order
     * @param bool   $return
     *
     * @return string|array
     */
    public function selectOne($table, $array = [], $conditions = [], $order = [], $return = false)
    {
        $limit = 1;

        $result = $this->select($table, $array, $conditions, $order, $limit, $return);

        if (!is_string($result) && isset($result[0])) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Costruisce la query per l'DELETE definito dagli argomenti.
     *
     * @since 2.4.1
     *
     * @param string $table
     * @param array  $conditions
     *
     * @return string|array
     */
    public function delete($table, $conditions)
    {
        if (!is_string($table) || !is_array($conditions)) {
            throw new UnexpectedValueException();
        }

        // Costruzione della query
        return DB::table($table)->where($conditions)->delete();
    }

    /**
     * Sincronizza i valori indicati associati alle condizioni, rimuovendo le combinazioni aggiuntive e inserendo quelle non ancora presenti.
     *
     * @since 2.3
     *
     * @param string $table
     * @param array  $conditions Condizioni di sincronizzazione
     * @param array  $list       Valori da sincronizzare
     */
    public function sync($table, $conditions, $list)
    {
        if (
            !is_string($table) ||
            !is_array($conditions) ||
            !is_array($list)
            ) {
            throw new UnexpectedValueException();
        }

        $field = key($list);
        $sync = array_unique((array) current($list));

        if (!empty($field)) {
            $results = array_column($this->select($table, $field, $conditions), $field);

            $detachs = array_unique(array_diff($results, $sync));
            $this->detach($table, $conditions, [$field => $detachs]);

            $this->attach($table, $conditions, $list);
        }
    }

    /**
     * Inserisce le le combinazioni tra i valori indicati e le condizioni.
     *
     * @since 2.3
     *
     * @param string $table
     * @param array  $conditions Condizioni
     * @param array  $list       Valori da aggiungere
     */
    public function attach($table, $conditions, $list)
    {
        if (
            !is_string($table) ||
            !is_array($conditions) ||
            !is_array($list)
            ) {
            throw new UnexpectedValueException();
        }

        $field = key($list);
        $sync = array_unique((array) current($list));
        $inserts = [];

        if (!empty($field)) {
            $results = array_column($this->select($table, $field, $conditions), $field);

            $inserts = array_unique(array_diff($sync, $results));
            foreach ($inserts as $insert) {
                $this->insert($table, array_merge($conditions, [$field => $insert]));
            }
        }

        return count($inserts);
    }

    /**
     * Rimuove le le combinazioni tra i valori indicati e le condizioni.
     *
     * @since 2.3
     *
     * @param string $table
     * @param array  $conditions Condizioni
     * @param array  $list       Valori da rimuovere
     */
    public function detach($table, $conditions, $list)
    {
        if (
            !is_string($table) ||
            !is_array($conditions) ||
            !is_array($list)
            ) {
            throw new UnexpectedValueException();
        }

        $field = key($list);
        $sync = array_unique((array) current($list));

        if (!empty($field) && !empty($sync)) {
            foreach ($sync as $element) {
                $conditions[$field] = $element;

                $this->delete($table, $conditions);
            }
        }
    }

    public function beginTransaction()
    {
        DB::beginTransaction();
    }

    public function commitTransaction()
    {
        DB::commit();
    }

    public function rollbackTransaction()
    {
        DB::rollBack();
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        return DB::raw($value);
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param string $table
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table)
    {
        return DB::table($table);
    }

    /**
     * Esegue le query interne ad un file ".sql".
     *
     * @since 2.0
     *
     * @param string $filename  Percorso per raggiungere il file delle query
     * @param string $delimiter Delimitatore delle query
     */
    public function multiQuery($filename, $start = 0)
    {
        $queries = readSQLFile($filename, ';');
        $end = count($queries);

        for ($i = $start; $i < $end; ++$i) {
            $this->query($queries[$i]);
        }

        return true;
    }
}
