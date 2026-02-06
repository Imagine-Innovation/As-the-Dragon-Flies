<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class SendingMessageEvent extends Event
{
    public string $message;

    /**
     *
     * @param string $sessionId
     * @param Player $player
     * @param Quest $quest
     * @param string $message
     * @param array<string, mixed> $config
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, string $message, array $config = [])
    {
        parent::__construct($sessionId, $player, $quest, $config);
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string
    {
        return 'new-message';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Sending new message';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'message' => $this->message,
            'sentAt' => date('Y-m-d H:i:s', $this->timestamp),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** SendingMessageEvent - process');
        $notification = $this->createNotification();

        $this->savePlayerNotifications($notification->id);

        $this->broadcast();
    }
}
