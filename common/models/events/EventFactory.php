<?php

namespace common\models\events;

use common\helpers\PayloadHelper;
use common\models\events\Event;
use common\models\Player;
use common\models\Quest;
use Yii;

class EventFactory
{

    /**
     *
     * @param string $eventType
     * @param Player $player
     * @param Quest $quest
     * @param array<string, mixed> $data
     * @return Event
     */
    public static function createEvent(
            string $eventType,
            Player $player,
            Quest $quest,
            array $data = [],
    ): Event
    {
        Yii::debug("*** debug *** EventFactory.createEvent type={$eventType}, playerId={$player->id}, questId={$quest->id}");

        $reason = PayloadHelper::extractStringFromPayload('reason', $data, 'Unknown reason');
        $message = PayloadHelper::extractStringFromPayload('message', $data, '');
        $action = PayloadHelper::extractStringFromPayload('action', $data, '');
        $detail = PayloadHelper::extractArrayFromPayload('detail', $data);
        return match ($eventType) {
            'player-joining' => new PlayerJoiningEvent($player, $quest),
            'player-quitting' => new PlayerQuittingEvent($player, $quest, $reason),
            'quest-starting' => new QuestStartingEvent($player, $quest),
            'sending-message' => new SendingMessageEvent($player, $quest, $message),
            'game-action' => new GameActionEvent($player, $quest, $action, $detail),
            'next-turn' => new NextTurnEvent($player, $quest, $action, $detail),
            'next-mission' => new NextMissionEvent($player, $quest, $action, $detail),
            'game-over' => new GameOverEvent($player, $quest, $action, $detail),
            default => throw new \InvalidArgumentException("Unknown event type: $eventType"),
        };
    }
}
