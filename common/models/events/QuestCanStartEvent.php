<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class QuestCanStartEvent extends Event
{

    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = []) {
        parent::__construct($sessionId, $player, $quest, $config);
    }

    public function getType(): string {
        return 'quest-can-start';
    }

    public function getTitle(): string {
        return 'Quest can start';
    }

    public function getMessage(): string {
        return "The quest {$this->quest->story->name} can now start.";
    }

    public function getPayload(): array {
        return [
            'questName' => ($this->quest) ? $this->quest->story->name : null,
            'questId' => ($this->quest) ? $this->quest->id : null,
        ];
    }

    public function process(): void {
        Yii::debug("*** Debug *** QuestCanStartEvent - process");
        $this->broadcast();
    }
}
