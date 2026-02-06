<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class PlayerQuittingEvent extends Event
{
    public string $reason;

    /**
     *
     * @param string $sessionId
     * @param Player $player
     * @param Quest $quest
     * @param string $reason
     * @param array<string, mixed> $config
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, string $reason, array $config = [])
    {
        parent::__construct($sessionId, $player, $quest, $config);
        $this->reason = $reason;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string
    {
        return 'player-quit';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Player quitting';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        return "{$this->player->name} is quitting the quest";
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questName' => $this->quest->name,
            'questId' => $this->quest->id,
            'leftAt' => date('Y-m-d H:i:s', $this->timestamp),
            'reason' => $this->reason,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** PlayerQuittingEvent - process');
        $notification = $this->createNotification();

        $this->savePlayerNotifications($notification->id);

        $this->broadcast();
        Yii::debug('*** Debug *** PlayerJoiningEvent - process');

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = "Player {$this->player->name} has quit the quest. Reason: {$this->reason}";
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
