<?php

namespace common\extensions\EventHandler\contracts;

use Ratchet\ConnectionInterface;

interface MessageHandlerInterface {
    public function handle(ConnectionInterface $conn, string $clientId, string $message): void;
    public function open(ConnectionInterface $conn, string $clientId): void;
    public function close(string $clientId): void;
    public function error(ConnectionInterface $conn, string $clientId, \Exception $e): void;
}
