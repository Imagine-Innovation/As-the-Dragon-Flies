<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

class QuestStartingEvent extends Event
{
    /**
     *
     * @param string $sessionId
     * @param Player $player
     * @param Quest $quest
     * @param array<string, mixed> $config
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = [])
    {
        parent::__construct($sessionId, $player, $quest, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string
    {
        return 'quest-started';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Quest Starting';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        return "The quest {$this->quest->name} is starting";
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'questName' => $this->quest->name,
            'questId' => $this->quest->id,
            'startedAt' => date('Y-m-d H:i:s', $this->timestamp),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** QuestStartingEvent - process');
        $notification = $this->createNotification();

        $this->savePlayerNotifications($notification->id);

        $this->broadcast();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        $questName = $this->quest->name;
        if ($dungeonMaster) {
            $message = "{$this->player->name} has started quest '{$questName}'!";
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
