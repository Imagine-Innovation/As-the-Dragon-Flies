<?php

namespace common\models\events;

use common\models\events\SendingMessageEvent;
use common\models\Player;
use common\models\Quest;
use Yii;

class PlayerJoiningEvent extends Event
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
        return 'player-joined';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Player Joined';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        return Yii::t('app/game', '{playerName} joins the quest', [
            'playerName' => $this->player->name,
        ], $this->language);
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
            'questName' => $this->quest->name,
            'joinedAt' => date('Y-m-d H:i:s', $this->timestamp),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** PlayerJoiningEvent - process');
        $notification = $this->createNotification();

        $this->broadcast();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = $this->getMessage();
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
