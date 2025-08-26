<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use common\models\events\NewMessageEvent;
use Yii;

class NewPlayerEvent extends Event
{

    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = []) {
        parent::__construct($sessionId, $player, $quest, $config);
    }

    public function getType(): string {
        return 'new-player';
    }

    public function getTitle(): string {
        return 'New player';
    }

    public function getMessage(): string {
        return "{$this->player->name} joined the quest";
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
        Yii::debug("*** Debug *** NewPlayerEvent - process");
        // Create notification
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = "Player {$this->player->name} has joined the quest";
            $newMessageEvent = new NewMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $newMessageEvent->process();
        }

        // Check if quest should start (max players reached)
        if (count($this->quest->currentPlayers) >= $this->quest->story->max_players) {
            $startQuestEvent = new StartQuestEvent($this->sessionId, $this->player, $this->quest);
            $startQuestEvent->process();
        }
    }
}
