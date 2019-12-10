<?php

namespace Util;

/**
 * Classe dedicata alla gestione dei messaggi per l'utente.
 *
 * @since 2.4.2
 */
class Messages extends \Slim\Flash\Messages
{
    /**
     * Create new Flash messages service provider.
     *
     * @param array|ArrayAccess|null $storage
     *
     * @throws RuntimeException         if the session cannot be found
     * @throws InvalidArgumentException if the store is not array-like
     */
    public function __construct(&$storage = null, $storageKey = null)
    {
        if (is_string($storageKey) && $storageKey) {
            $this->storageKey = $storageKey;
        }

        // Set storage
        if (is_array($storage) || $storage instanceof ArrayAccess) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (!isset($_SESSION)) {
                throw new RuntimeException('Flash messages middleware failed. Session not found.');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new InvalidArgumentException('Flash messages storage must be an array or implement \ArrayAccess');
        }

        // Load messages from previous request
        if (isset($this->storage[$this->storageKey]) && is_array($this->storage[$this->storageKey])) {
            $this->fromPrevious = $this->storage[$this->storageKey];
        }
    }

    public function getMessage($key)
    {
        $result = parent::getMessage($key);
        $this->clearMessages($key);

        return $result;
    }

    public function info($message)
    {
        return $this->addMessage('info', $message);
    }

    public function warning($message)
    {
        return $this->addMessage('warning', $message);
    }

    public function error($message)
    {
        return $this->addMessage('error', $message);
    }
}
