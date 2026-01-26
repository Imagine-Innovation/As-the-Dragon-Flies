<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */
class NextMissionEvent extends Event
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
        return 'next-mission';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string {
        return $this->action ?? 'Next mission';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string {
        /** @var array{currentPlayerName: string, currentMissionName: string, nextPlayerName: string, nextMissionName: string} */
        $detail = $this->detail;

        return "{$detail['currentPlayerName']} has completed mission “{$detail['currentMissionName']}”.\n"
                . "Now it's {$detail['nextPlayerName']}'s turn to start mission “{$detail['nextMissionName']}”.";
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
        Yii::debug("*** Debug *** NextMissionEvent - process");
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
