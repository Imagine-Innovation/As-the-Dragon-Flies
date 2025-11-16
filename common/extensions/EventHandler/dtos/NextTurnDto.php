<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class NextTurnDto implements BroadcastMessageInterface
{

    private string $type = 'next-turn';
    private array $payload;

    public function __construct(array $detail) {
        $this->payload = [
            'detail' => $detail,
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
