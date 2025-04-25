<?php

namespace common\models\events;

use common\models\Notification;
use common\models\Player;
use common\models\Quest;
use yii\base\BaseObject;

abstract class Event extends BaseObject {

    public $player;
    public $quest;
    public $timestamp;

    public function __construct(Player $player, Quest $quest, $config = []) {
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
        return [
            'type' => $this->getType(),
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'timestamp' => $this->timestamp,
            'payload' => $this->getPayload()
        ];
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
        $notification->save();

        return $notification;
    }
}
