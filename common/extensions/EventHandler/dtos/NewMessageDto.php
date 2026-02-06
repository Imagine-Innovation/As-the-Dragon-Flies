<?php

namespace common\extensions\EventHandler\dtos;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class NewMessageDto implements BroadcastMessageInterface
{
    private string $type = 'new-message';

    /** @var array<string, mixed> $payload */
    private array $payload;

    /**
     *
     * @param string $message
     * @param string $sender
     * @param string|null $recipient
     */
    public function __construct(string $message, string $sender, ?string $recipient = null)
    {
        $this->payload = [
            'message' => $message,
            'sender' => $sender,
            'recipient' => $recipient,
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
