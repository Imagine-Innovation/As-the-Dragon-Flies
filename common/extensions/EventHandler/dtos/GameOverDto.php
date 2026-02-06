<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class GameOverDto implements BroadcastMessageInterface
{
    private string $type = 'game-over';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param array<string, mixed> $detail
     */
    public function __construct(array $detail)
    {
        $this->payload = [
            'detail' => $detail,
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
