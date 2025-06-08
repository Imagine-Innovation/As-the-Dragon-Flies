<?php

namespace common\extensions\EventHandler;

use Ratchet\ConnectionInterface;

interface SpecificMessageHandlerInterface {
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void;
}
