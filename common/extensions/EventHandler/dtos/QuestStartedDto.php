<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class QuestStartedDto implements BroadcastMessageInterface
{
    public string $type = 'quest-started';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $sessionId
     * @param int $questId
     * @param string $questName
     */
    public function __construct(string $sessionId, int $questId, string $questName)
    {
        $this->payload = [
            'sessionId' => $sessionId,
            'questId' => $questId,
            'questName' => $questName,
            'message' => "Quest '{$questName}' has started!",
            'redirectUrl' => '/frontend/web/index.php?r=game/view&id=' . $questId,
            'timestamp' => time(),
            'startedAt' => date('Y-m-d H:i:s', time()),
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
