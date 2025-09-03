<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class MessageSentDto implements BroadcastMessageInterface
{

    private string $type = 'message-sent';
    private array $payload;

    public function __construct(string $message, string $sender, ?string $recipient = null) {
        $this->payload = [
            'message' => $message,
            'sender' => $sender,
            'timestamp' => time()
        ];
        if ($recipient !== null) {
            $this->payload['recipient'] = $recipient;
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
