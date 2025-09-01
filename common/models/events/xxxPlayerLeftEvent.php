<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class PlayerLeftEvent extends Event
{

    public $reason;

    public function __construct(string $sessionId, Player $player, Quest $quest, $reason) {
        parent::__construct($sessionId, $player, $quest);
        $this->reason = $reason;
    }

    public function getType(): string {
        return 'player-left';
    }

    public function getTitle(): string {
        return 'Player left the quest';
    }

    public function getMessage(): string {
        return "{$this->player->name} has left the quest";
    }

    public function getPayload(): array {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questName' => ($this->quest) ? $this->quest->story->name : null,
            'questId' => ($this->quest) ? $this->quest->id : null,
            'leftAt' => date('Y-m-d H:i:s', $this->timestamp),
            'reason' => $this->reason,
        ];
    }

    public function process(): void {
        // Create notification
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();
    }
}
