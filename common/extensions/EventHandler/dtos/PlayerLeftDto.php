<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class PlayerLeftDto implements BroadcastMessageInterface {

    private string $type = 'player_left';
    private array $payload;

    public function __construct(string $playerName, string $sessionId, string $questName) {
        $this->payload = [
            'playerName' => $playerName,
            'sessionId' => $sessionId,
            'questName' => $questName,
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
