<?php

namespace common\extensions\EventHandler\contracts;

use Ratchet\ConnectionInterface;

interface SpecificMessageHandlerInterface {
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void;
}
