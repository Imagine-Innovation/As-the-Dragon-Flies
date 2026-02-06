<?php

namespace common\extensions\EventHandler\contracts;

use common\models\QuestSession;
use Ratchet\ConnectionInterface;

interface BroadcastServiceInterface
{
    public function sendToConnection(ConnectionInterface $connection, string $jsonData): void;

    public function sendBack(ConnectionInterface $to, string $type, mixed $message): void;

    public function sendToClient(
        string $clientId,
        BroadcastMessageInterface $message,
        bool $updateTimestamp = true,
        ?string $sessionId = null,
    ): bool;

    public function sendToSession(
        QuestSession $session,
        BroadcastMessageInterface $message,
        bool $updateTimestamp = true,
    ): bool;

    public function broadcast(BroadcastMessageInterface $message): void;

    public function broadcastToQuest(
        int $questId,
        BroadcastMessageInterface $message,
        ?string $excludeSessionId = null,
    ): void;

    public function recoverMessageHistory(string $sessionId): void;
}
