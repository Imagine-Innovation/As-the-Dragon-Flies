<?php

namespace common\models\events;

use common\components\AppStatus;
use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */
class GameActionEvent extends Event
{
    /** @var string The action type */
    public $action;

    /** @var array<string, mixed> Additional action data */
    public $detail;

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string $action The action type
     * @param array<string, mixed> $detail Additional action data
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, string $action, array $detail = [])
    {
        parent::__construct($sessionId, $player, $quest);
        $this->action = $action;
        $this->detail = $detail;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string
    {
        return 'game-action';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'New action';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        /** @var AppStatus */
        $status = $this->detail['status'];
        Yii::debug("*** debug *** GameActionEvent->getMessage status={$status->getLabel()}");
        return match ($status->value) {
            AppStatus::SUCCESS->value => "{$this->player->name} successfully completed the “{$this->action}” action",
            AppStatus::PARTIAL->value => "{$this->player->name} partially completed the “{$this->action}” action",
            AppStatus::FAILURE->value => "{$this->player->name} failed to complete the “{$this->action}” action",
            default => "{$this->player->name} completed the “{$this->action}” action with an unknown status",
        };
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'playerId' => $this->player->id,
            'playerName' => $this->player->name,
            'action' => $this->action,
            'questId' => $this->quest->id,
            'questName' => $this->quest->name,
            'detail' => $this->detail,
            //'timestamp' => date('Y-m-d H:i:s', $this->timestamp)
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** GameActionEvent - process');
        $notification = $this->createNotification();

        $this->savePlayerNotifications($notification->id);

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
