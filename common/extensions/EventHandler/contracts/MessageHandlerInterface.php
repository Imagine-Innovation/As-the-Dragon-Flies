<?php

namespace common\extensions\EventHandler\contracts;

use Ratchet\ConnectionInterface;

interface MessageHandlerInterface
{
    /**
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @param string $message
     * @return void
     */
    public function handle(ConnectionInterface $conn, string $clientId, string $message): void;

    /**
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @return void
     */
    public function open(ConnectionInterface $conn, string $clientId): void;

    /**
     *
     * @param string $clientId
     * @return void
     */
    public function close(string $clientId): void;

    /**
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @param \Exception $e
     * @return void
     */
    public function error(ConnectionInterface $conn, string $clientId, \Exception $e): void;
}
