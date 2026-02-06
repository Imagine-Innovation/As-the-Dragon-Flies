<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class NotificationDto implements BroadcastMessageInterface
{
    private string $type = 'notification';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $message
     * @param string $level
     * @param array<string, mixed>|null $details
     */
    public function __construct(string $message, string $level = 'info', ?array $details = null)
    {
        $this->payload = [
            'message' => $message,
            'level' => $level, // e.g., info, warning, error
            'timestamp' => time(),
        ];
        if ($details !== null) {
            $this->payload['details'] = $details;
        }
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
