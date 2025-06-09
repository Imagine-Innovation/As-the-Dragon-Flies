<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class PlayerJoinedDto implements BroadcastMessageInterface {
    private string $type = 'player_joined';
    private array $payload;

    public function __construct(string $playerName, string $sessionId, string $questName) {
        $this->payload = [
            'playerName' => $playerName,
            'sessionId' => $sessionId,
            'questName' => $questName, // Added this line
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
