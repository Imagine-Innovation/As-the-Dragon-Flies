<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class NotificationDto implements BroadcastMessageInterface {
    private string $type = 'notification';
    private array $payload;

    public function __construct(string $message, string $level = 'info', ?array $details = null) {
        $this->payload = [
            'message' => $message,
            'level' => $level, // e.g., info, warning, error
            'timestamp' => time()
        ];
        if ($details !== null) {
            $this->payload['details'] = $details;
        }
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
