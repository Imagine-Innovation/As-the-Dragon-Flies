<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class QuestCanStartDto implements BroadcastMessageInterface {

    private string $type = 'quest_can_start';
    private array $payload;

    public function __construct(string $sessionId, string $questName) {
        $this->payload = [
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
