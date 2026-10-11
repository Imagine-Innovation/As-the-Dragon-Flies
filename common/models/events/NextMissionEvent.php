<?php

namespace common\models\events;

use common\helpers\LocalizeMessageHelper;
use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */

/**
 * @phpstan-type T array{
 *     currentMissionId: int,
 *     currentMissionName: string,
 *     currentPlayerId: int,
 *     currentPlayerName: string,
 *     nextMissionId: int,
 *     nextMissionName: string,
 *     nextPlayerId: int,
 *     nextPlayerName: string,
 *     nextQuestProgressId: int,
 *     toastMessage: array{current: string, other: string},
 *     turnMessage: array{current: string, other: string},
 *     timestamp: int
 * }
 */
class NextMissionEvent extends Event
{

    /** @var string|null The action type */
    public ?string $action = null;

    /** @var T|array{} $detail Additional action data */
    public $detail;

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string|null $action The action type
     * @param T|array{} $detail Additional action data
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
        if (!isset($this->detail['toastMessage'])) {
            /** @var T $detail */
            $detail = $this->detail;

            $toastMessage = LocalizeMessageHelper::nextMissionToastMessage(
                    $detail['currentPlayerName'],
                    $detail['currentMissionName'],
                    $detail['nextPlayerName'],
                    $detail['nextMissionName']
            );
            Yii::debug($toastMessage['other']);
            return $toastMessage['other'];
        }

        Yii::debug($this->detail['toastMessage']['other']);
        return $this->detail['toastMessage']['other'];
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
