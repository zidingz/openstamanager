<?php

class Router extends \Slim\Router
{
    /**
     * Metodo sovrascritto per la rimozione dei valori a null del percorso.
     *
     * @param string $name
     * @param array  $data
     * @param array  $queryParams
     *
     * @return string
     */
    public function pathFor($name, array $data = [], array $queryParams = [])
    {
        foreach ($data as $key => $value) {
            if (!isset($value)) {
                unset($data[$key]);
            }
        }

        return parent::pathFor($name, $data, $queryParams);
    }

    public function hasRoute($name)
    {
        try {
            return parent::getNamedRoute($name);
        } catch (RuntimeException $e) {
            return false;
        }
    }
}
