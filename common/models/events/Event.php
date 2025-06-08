<?php

namespace common\models\events;

use common\models\Notification;
use common\models\Player;
use common\models\Quest;
use Yii;
use yii\base\BaseObject;

abstract class Event extends BaseObject {

    public $sessionId;
    public $player;
    public $quest;
    public $timestamp;
    public $notificationId;

    public function __construct(string $sessionId, Player $player, Quest $quest, $config = []) {
        $this->notificationId = null;
        $this->sessionId = $sessionId;
        $this->player = $player;
        $this->quest = $quest;
        $this->timestamp = time();
        parent::__construct($config);
    }

    abstract public function getType(): string;

    abstract public function getPayload(): array;

    abstract public function getTitle(): string;

    abstract public function getMessage(): string;

    abstract public function process(): void;

    public function toArray(): array {
        $array = [
            'type' => $this->getType(),
            'notificationId' => $this->notificationId,
            'sessionId' => $this->sessionId,
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'timestamp' => $this->timestamp,
            'payload' => $this->getPayload()
        ];
        Yii::debug($array);
        return $array;
    }

    protected function broadcast() {
        $array = $this->toArray();

        // First, register the session for the quest
        if (Yii::$app->eventHandler->registerSessionForQuest($this->sessionId, $array)) {
            // Broadcast event to all connected clients
            Yii::$app->eventHandler->broadcastToQuest(
                    $this->quest->id,
                    $array,
                    $this->sessionId
            );
        }
    }

    protected function createNotification(): Notification {
        $notification = new Notification([
            'player_id' => $this->player->id,
            'quest_id' => $this->quest->id,
            'notification_type' => $this->getType(),
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'created_at' => time(),
            'payload' => json_encode($this->getPayload()),
            'is_private' => 0,
        ]);
        $this->notificationId = ($notification->save()) ? $notification->id : null;

        return $notification;
    }
}
