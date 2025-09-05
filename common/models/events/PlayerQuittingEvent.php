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
        return 'player-quit';
    }

    public function getTitle(): string {
        return 'Player quitting';
    }

    public function getMessage(): string {
        return "{$this->player->name} is quitting the quest";
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
        Yii::debug("*** Debug *** PlayerJoiningEvent - process");

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = "Player {$this->player->name} has quit the quest. Reason: {$this->reason}";
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
