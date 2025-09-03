<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class SendingMessageEvent extends Event
{

    public $message;

    public function __construct(string $sessionId, Player $player, Quest $quest, string $message, array $config = []) {
        parent::__construct($sessionId, $player, $quest, $config);
        $this->message = $message;
    }

    public function getType(): string {
        return 'sending-message';
    }

    public function getTitle(): string {
        return 'Sending Message';
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getPayload(): array {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'message' => $this->message,
            'sentAt' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        Yii::debug("*** Debug *** SendingMessageEvent - process");
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();
    }
}
