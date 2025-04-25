<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;

class EventFactory {

    public static function createEvent($type, Player $player, Quest $quest, $data = []) {

        return match ($type) {
            'new-player' => new NewPlayerEvent($player, $quest),
            'new-message' => new NewMessageEvent($player, $quest, $data['message'] ?? ''),
            'start-quest' => new StartQuestEvent($player, $quest),
            'game-action' => new GameActionEvent($player, $quest, $data['action'] ?? '', $data['actionData'] ?? []),
            default => throw new \InvalidArgumentException("Unknown event type: $type"),
        };

        /* Original version */
        switch ($type) {
            case 'new-player':
                return new NewPlayerEvent($player, $quest);

            case 'new-message':
                return new NewMessageEvent($player, $quest, $data['message'] ?? '');

            case 'start-quest':
                return new StartQuestEvent($player, $quest);

            case 'game-action':
                return new GameActionEvent($player, $quest, $data['action'] ?? '', $data['actionData'] ?? []);

            default:
                throw new \InvalidArgumentException("Unknown event type: $type");
        }
    }
}
