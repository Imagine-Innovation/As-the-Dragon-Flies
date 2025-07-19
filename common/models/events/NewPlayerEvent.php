<?php

namespace common\models\events;

use Yii;

class NewPlayerEvent extends Event {

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

        // Check if quest should start (max players reached)
        if (count($players) >= $this->quest->story->max_players) {
            $startQuestEvent = new StartQuestEvent($this->player, $this->quest);
            $startQuestEvent->process();
        }
    }
}
