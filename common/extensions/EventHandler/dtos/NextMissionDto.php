<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class NextMissionDto implements BroadcastMessageInterface
{

    private string $type = 'next-mission';
    private array $payload;

    public function __construct(int $playerId, string $playerName, string $sessionId, string $questName, int $missionId, string $missionName) {
        $this->payload = [
            'sessionId' => $sessionId,
            'missionId' => $missionId,
            'playerId' => $playerId,
            'questName' => $questName,
            'missionName' => $missionName,
            'playerName' => $playerName,
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
