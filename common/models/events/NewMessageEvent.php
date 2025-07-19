<?php

namespace common\models\events;

use common\models\Quest;
use common\models\Player;
use frontend\components\QuestMessages;
use Yii;

class NewMessageEvent extends Event {

    public $message;

    public function __construct(string $sessionId, Player $player, Quest $quest, string $message, array $config = []) {
        $this->message = $message;
        parent::__construct($sessionId, $player, $quest, $config);
    }

    public function getType(): string {
        return 'new-message';
    }

    public function getTitle(): string {
        return 'New message';
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getPayload(): array {
        return QuestMessages::payload($this->player, $this->message);
    }

    public function process(): void {
        Yii::debug("*** Debug *** NewMessageEvent - process");
        // Create notification
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();
    }
}
