<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use common\models\events\NewMessageEvent;
use Yii;

class QuestStartingEvent extends Event
{

    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = []) {
        parent::__construct($sessionId, $player, $quest, $config);
    }

    public function getType(): string {
        return 'quest_starting';
    }

    public function getTitle(): string {
        return 'Quest is starting';
    }

    public function getMessage(): string {
        return "{$this->quest->story->name} is starting";
    }

    public function getPayload(): array {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questName' => $this->quest->story->name,
            'questId' => $this->quest->id,
            'startedAt' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        Yii::debug("*** Debug *** QuestStartingEvent - process");
        $this->broadcast();
    }
}
