<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class PlayerQuitDto implements BroadcastMessageInterface
{

    private string $type = 'player-quit';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $playerName
     * @param string $sessionId
     * @param string $questName
     * @param string $reason
     */
    public function __construct(string $playerName, string $sessionId, string $questName, string $reason) {
        $this->payload = [
            'playerName' => $playerName,
            'sessionId' => $sessionId,
            'questName' => $questName,
            'reason' => $reason,
            'timestamp' => time(),
        ];
    }

    /**
     *
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array {
        return $this->payload;
    }

    /**
     *
     * @return string
     */
    public function toJson(): string {
        return json_encode(['type' => $this->type, 'payload' => $this->payload]);
    }
}
