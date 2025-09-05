<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class NewMessageDto implements BroadcastMessageInterface
{

    private string $type = 'new-message';
    private array $payload;

    public function __construct(string $message, string $sender, ?string $recipient = null) {
        $this->payload = [
            'message' => $message,
            'sender' => $sender,
            'recipient' => $recipient,
            'timestamp' => time(),
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
