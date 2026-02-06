<?php

namespace common\extensions\EventHandler\contracts;

use Ratchet\ConnectionInterface;

interface SpecificMessageHandlerInterface
{
    /**
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void;
}
