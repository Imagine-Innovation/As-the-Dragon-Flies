<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class QuestStartedDto implements BroadcastMessageInterface
{

    public string $type = 'quest-started';
    private array $payload;

    public function __construct(string $sessionId, int $questId, string $questName) {
        $this->payload = [
            'sessionId' => $sessionId,
            'questId' => $questId,
            'questName' => $questName,
            'message' => "Quest '{$questName}' has started!",
            'redirectUrl' => '/frontend/web/index.php?r=game/view&id=' . $questId,
            'timestamp' => time(),
            'startedAt' => date('Y-m-d H:i:s', time())
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
