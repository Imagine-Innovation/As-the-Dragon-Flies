<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class PlayerQuittingEvent extends Event
{

    public $reason;

    public function __construct(string $sessionId, Player $player, Quest $quest, $reason, array $config = []) {
        parent::__construct($sessionId, $player, $quest, $config);
        $this->reason = $reason;
    }

    public function getType(): string {
        return 'player-quitting';
    }

    public function getTitle(): string {
        return 'Player Leaving';
    }

    public function getMessage(): string {
        return "{$this->player->name} is leaving the quest";
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
        Yii::debug("*** Debug *** PlayerQuittingEvent - process");
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();
    }
}
