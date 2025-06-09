<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class GameActionDto implements BroadcastMessageInterface {
    private string $type = 'game_action';
    private array $payload;

    public function __construct(string $action, array $details) {
        $this->payload = [
            'action' => $action,
            'details' => $details,
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
