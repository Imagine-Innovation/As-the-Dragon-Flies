<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class GameActionDto implements BroadcastMessageInterface
{

    private string $type = 'game-action';
    private array $payload;

    public function __construct(string $playerName, string $action, array $detail) {
        $this->payload = [
            'playerName' => $playerName,
            'action' => $action,
            'detail' => $detail,
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
