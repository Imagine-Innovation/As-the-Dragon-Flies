<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */
class NextTurnEvent extends Event
{

    /** @var string|null The action type */
    public ?string $action = null;

    /** @var array<string, mixed> Additional action data */
    public $detail = [];

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string|null $action The action type
     * @param array<string, mixed> $detail Additional action data
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, ?string $action, array $detail = []) {
        parent::__construct($sessionId, $player, $quest);
        $this->action = $action;
        $this->detail = $detail;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string {
        return 'next-turn';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string {
        return $this->action ?? 'Next turn';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string {
        /** @var array{currentPlayerName: string, nextPlayerName: string} */
        $detail = $this->detail;

        return "{$detail['currentPlayerName']} has finished his turn. Now it's {$detail['nextPlayerName']}'s turn to play.";
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array {
        return [
            'detail' => $this->detail,
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void {
        Yii::debug("*** Debug *** NextTurnEvent - process");
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
