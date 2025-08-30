<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class QuestCanStartDto implements BroadcastMessageInterface
{

    private string $type = 'quest-can-start';
    private array $payload;

    public function __construct(string $sessionId, string $questName, ?int $questId = null) {
        $this->payload = [
            'sessionId' => $sessionId,
            'questName' => $questName,
            'questId' => $questId,
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
