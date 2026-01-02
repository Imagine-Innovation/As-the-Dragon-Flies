<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class GameActionDto implements BroadcastMessageInterface
{

    private string $type = 'game-action';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $playerName
     * @param string $action
     * @param array<string, mixed> $detail
     */
    public function __construct(string $playerName, string $action, array $detail) {
        $this->payload = [
            'playerName' => $playerName,
            'action' => $action,
            'detail' => $detail,
            'timestamp' => time()
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
     * @return string|false
     */
    public function toJson(): string|false {
        return json_encode(['type' => $this->type, 'payload' => $this->payload]);
    }
}
