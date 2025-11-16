<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */
class GameOverEvent extends Event
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
        return 'game-over';
    }

    public function getTitle(): string {
        return $this->action ?? 'Game over';
    }

    public function getMessage(): string {
        $detail = $this->detail;

        return "{$detail['playerName']} has ended quest “{$detail['questName']}” with status {$detail['status']}.";
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload(): array {
        return [
            'detail' => $this->detail,
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(): void {
        Yii::debug("*** Debug *** GameOverEvent - process");
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

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
