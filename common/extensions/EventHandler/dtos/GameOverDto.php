<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class GameOverDto implements BroadcastMessageInterface
{

    private string $type = 'game-over';
    private array $payload;

    public function __construct(string $status, string $playerName, string $sessionId, string $questName) {
        $this->payload = [
            'sessionId' => $sessionId,
            'questName' => $questName,
            'playerName' => $playerName,
            'status' => $status,
            'timestamp' => time()
        ];
    }

    public function getType(): string {
        return $this->type;
    }

    public function getPayload(): array {
        return $this->payload;
    }

    public function toJson(): string {
        return json_encode(['type' => $this->type, 'payload' => $this->payload]);
    }
}
