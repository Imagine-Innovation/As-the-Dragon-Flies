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
     * @return type
     */
    public static function createEvent(string $eventType, string $sessionId, Player $player, Quest $quest, array $data = []) {

        Yii::debug("*** debug *** EventFactory.createEvent type=$eventType, sessionId={$sessionId}, playerId={$player->id}, questId={$quest->id}, data=" . print_r($data, true));
        return match ($eventType) {
            'player-joining' => new PlayerJoiningEvent($sessionId, $player, $quest),
            'player-leaving' => new PlayerLeavingEvent($sessionId, $player, $quest, $data['reason'] ?? 'Unknown reason'),
            'quest-starting' => new QuestStartingEvent($sessionId, $player, $quest),
            'sending-message' => new SendingMessageEvent($sessionId, $player, $quest, $data['message'] ?? ''),
            'quest-can-start' => new QuestCanStartEvent($sessionId, $player, $quest),
            'new-player' => new NewPlayerEvent($sessionId, $player, $quest),
            'player-left' => new PlayerLeftEvent($sessionId, $player, $quest, $data['reason'] ?? 'Unknown reason'),
            'new-message' => new NewMessageEvent($sessionId, $player, $quest, $data['message'] ?? ''),
            'start-quest' => new StartQuestEvent($sessionId, $player, $quest),
            'game-action' => new GameActionEvent($sessionId, $player, $quest, $data['action'] ?? '', $data['actionData'] ?? []),
            default => throw new \InvalidArgumentException("Unknown event type: $eventType"),
        };
    }
}
