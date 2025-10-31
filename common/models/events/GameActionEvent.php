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

    /**
     * @var string The action type
     */
    public $action;

    /**
     * @var array Additional action data
     */
    public $detail;

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string $action The action type
     * @param array $detail Additional action data
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, string $action, array $detail = []) {
        parent::__construct($sessionId, $player, $quest);
        $this->action = $action;
        $this->detail = $detail;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string {
        return 'game-action';
    }

    public function getTitle(): string {
        return 'New action';
    }

    public function getMessage(): string {
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
     */
    public function getPayload(): array {
        return [
            'playerId' => $this->player->id,
            'playerName' => $this->player->name,
            'action' => $this->action,
            'detail' => $this->detail,
            'questId' => $this->quest->id,
            'questName' => $this->quest->name,
            //'timestamp' => date('Y-m-d H:i:s', $this->timestamp)
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(): void {
        Yii::debug("*** Debug *** GameActionEvent - process");
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            //$message = "Player {$this->player->name} made the action \"{$this->action}\"";
            $message = $this->getMessage();
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
