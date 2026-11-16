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
     * @param Player $player
     * @param Quest $quest
     * @param string $reason
     * @param array<string, mixed> $config
     */
    public function __construct(Player $player, Quest $quest, string $reason, array $config = [])
    {
        parent::__construct($player, $quest, $config);
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
        return Yii::t('app/game', '{playerName} quits the quest', [
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
        $this->createNotification();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = $this->getMessage();
            if ($this->reason !== '') {
                $message .= ' (' . $this->reason . ')';
            }
            $sendingMessageEvent = new SendingMessageEvent($dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
