<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class ErrorDto implements BroadcastMessageInterface
{

    private string $type = 'error';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $errorMessage
     * @param int|null $errorCode
     * @param array<string, mixed>|null $details
     */
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
