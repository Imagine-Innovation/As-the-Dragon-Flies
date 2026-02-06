<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class PlayerJoinedDto implements BroadcastMessageInterface
{
    private string $type = 'player-joined';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $playerName
     * @param string $sessionId
     * @param string $questName
     */
    public function __construct(string $playerName, string $sessionId, string $questName)
    {
        $this->payload = [
            'playerName' => $playerName,
            'sessionId' => $sessionId,
            'questName' => $questName,
            'timestamp' => time(),
        ];
    }

    /**
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     *
     * @return string|false
     */
    public function toJson(): string|false
    {
        return json_encode(['type' => $this->type, 'payload' => $this->payload]);
    }
}
