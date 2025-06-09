<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class ErrorDto implements BroadcastMessageInterface {
    private string $type = 'error';
    private array $payload;

    public function __construct(string $errorMessage, ?int $errorCode = null, ?array $details = null) {
        $this->payload = [
            'message' => $errorMessage,
            'timestamp' => time()
        ];
        if ($errorCode !== null) {
            $this->payload['code'] = $errorCode;
        }
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
