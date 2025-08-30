<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class QuestStartingEvent extends Event
{
    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = [])
    {
        parent::__construct($sessionId, $player, $quest, $config);
    }

    public function getType(): string
    {
        return 'quest-starting';
    }

    public function getTitle(): string
    {
        return 'Quest Starting';
    }

    public function getMessage(): string
    {
        return "The quest {$this->quest->story->name} is starting";
    }

    public function getPayload(): array
    {
        return [
            'questName' => ($this->quest) ? $this->quest->story->name : null,
            'questId' => ($this->quest) ? $this->quest->id : null,
            'startedAt' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void
    {
        Yii::debug("*** Debug *** QuestStartingEvent - process");
        $this->broadcast();
    }
}
