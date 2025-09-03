<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use common\models\events\SendingMessageEvent;
use Yii;

class PlayerJoiningEvent extends Event
{

    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = []) {
        parent::__construct($sessionId, $player, $quest, $config);
    }

    public function getType(): string {
        return 'player-joining';
    }

    public function getTitle(): string {
        return 'Player Joining';
    }

    public function getMessage(): string {
        return "{$this->player->name} is joining the quest";
    }

    public function getPayload(): array {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questName' => ($this->quest) ? $this->quest->story->name : null,
            'questId' => ($this->quest) ? $this->quest->id : null,
            'joinedAt' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        Yii::debug("*** Debug *** PlayerJoiningEvent - process");
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = "Player {$this->player->name} has joined the quest";
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
