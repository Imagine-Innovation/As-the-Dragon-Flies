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
    public function __construct(string $sessionId, Player $player, Quest $quest, ?string $action, array $detail = [])
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
        return 'next-mission';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->action ?? 'Next mission';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        /** @var array{currentPlayerName?: string, currentMissionName?: string, nextPlayerName?: string, nextMissionName?: string, toastMessage?: array{current: string, other: string}} */
        $detail = $this->detail;

        if (isset($detail['toastMessage']['other'])) {
            return $detail['toastMessage']['other'];
        }

        return Yii::t('app/game', '{currentPlayerName} completed “{currentMissionName}”. {nextPlayerName}’s turn: “{nextMissionName}”', [
            'currentPlayerName' => $detail['currentPlayerName'] ?? '',
            'currentMissionName' => $detail['currentMissionName'] ?? '',
            'nextPlayerName' => $detail['nextPlayerName'] ?? '',
            'nextMissionName' => $detail['nextMissionName'] ?? '',
        ], $this->language);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        $detail = $this->detail;
        $detail['timestamp'] = $this->timestamp;
        return [
            'detail' => $detail,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** NextMissionEvent - process');
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
