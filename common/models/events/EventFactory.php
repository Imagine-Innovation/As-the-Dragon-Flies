<?php

namespace common\models\events;

use Yii;
use common\models\Player;
use common\models\Quest;

class EventFactory
{

    /**
     *
     * @param string $eventType
     * @param string $sessionId
     * @param Player $player
     * @param Quest $quest
     * @param array $data
     * @return \common\models\events\Event
     */
    public static function createEvent(string $eventType, string $sessionId, Player $player, Quest $quest, array $data = []): Event {

        Yii::debug("*** debug *** EventFactory.createEvent type=$eventType, sessionId={$sessionId}, playerId={$player->id}, questId={$quest->id}, data=" . print_r($data, true));
        return match ($eventType) {
            'player-joining' => new PlayerJoiningEvent($sessionId, $player, $quest),
            'player-quitting' => new PlayerQuittingEvent($sessionId, $player, $quest, $data['reason'] ?? 'Unknown reason'),
            'quest-starting' => new QuestStartingEvent($sessionId, $player, $quest),
            'sending-message' => new SendingMessageEvent($sessionId, $player, $quest, $data['message'] ?? ''),
            'game-action' => new GameActionEvent($sessionId, $player, $quest, $data['action'] ?? '', $data['detail'] ?? []),
            'next-turn' => new NextTurnEvent($sessionId, $player, $quest, $data['action'] ?? '', $data['detail'] ?? []),
            'next-mission' => new NextMissionEvent($sessionId, $player, $quest, $data['action'] ?? '', $data['detail'] ?? []),
            'game-over' => new GameOverEvent($sessionId, $player, $quest, $data['action'] ?? '', $data['detail'] ?? []),
            default => throw new \InvalidArgumentException("Unknown event type: $eventType"),
        };
    }
}
