<?php

namespace common\models\events;

use common\models\events\Event;
use common\models\Player;
use common\models\Quest;
use Yii;

class EventFactory
{

    /**
     *
     * @param string $eventType
     * @param string $sessionId
     * @param Player $player
     * @param Quest $quest
     * @param array<string, mixed> $data
     * @return Event
     */
    public static function createEvent(string $eventType, string $sessionId, Player $player, Quest $quest, array $data = []): Event {

        Yii::debug("*** debug *** EventFactory.createEvent type=$eventType, sessionId={$sessionId}, playerId={$player->id}, questId={$quest->id}, data=" . print_r($data, true));

        $reason = is_string($data['reason']) ? (string) $data['reason'] : 'Unknown reason';
        $message = is_string($data['message']) ? (string) $data['message'] : '';
        $action = is_string($data['action']) ? (string) $data['action'] : '';
        $detail = is_array($data['detail']) ? (array) $data['detail'] : [];
        return match ($eventType) {
            'player-joining' => new PlayerJoiningEvent($sessionId, $player, $quest),
            'player-quitting' => new PlayerQuittingEvent($sessionId, $player, $quest, $reason),
            'quest-starting' => new QuestStartingEvent($sessionId, $player, $quest),
            'sending-message' => new SendingMessageEvent($sessionId, $player, $quest, $message),
            'game-action' => new GameActionEvent($sessionId, $player, $quest, $action, $detail),
            'next-turn' => new NextTurnEvent($sessionId, $player, $quest, $action, $detail),
            'next-mission' => new NextMissionEvent($sessionId, $player, $quest, $action, $detail),
            'game-over' => new GameOverEvent($sessionId, $player, $quest, $action, $detail),
            default => throw new \InvalidArgumentException("Unknown event type: $eventType"),
        };
    }
}
