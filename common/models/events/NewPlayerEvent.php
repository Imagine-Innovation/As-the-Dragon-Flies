<?php

namespace common\models\events;

use common\models\NotificationPlayer;
use Yii;

class NewPlayerEvent extends Event {

    public function getType(): string {
        return 'new-player';
    }

    public function getTitle(): string {
        return 'New player';
    }

    public function getMessage(): string {
        return "{$this->player->name} joined the quest";
    }

    public function getPayload(): array {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'questName' => ($this->quest) ? $this->quest->story->name : null,
            'questId' => ($this->quest) ? $this->quest->id : null,
            'joinedAt' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        // Create notification
        $notification = $this->createNotification();

        // Create notification_player entries for all players in quest
        $players = $this->quest->currentPlayers;
        foreach ($players as $player) {
            if ($player->id != $this->player->id) {
                $notificationPlayer = new NotificationPlayer([
                    'notification_id' => $notification->id,
                    'player_id' => $player->id,
                    'is_read' => 0
                ]);
                $notificationPlayer->save();
            }
        }

        $this->broadcast();
        /*
          $array = $this->toArray();
          // First, register the session for the quest
          if (Yii::$app->eventHandler->registerSessionForQuest($this->sessionId, $array)) {
          // Broadcast event to all connected clients
          Yii::$app->eventHandler->broadcastToQuest(
          $this->quest->id,
          $array
          );
          }
         *
         */

        // Check if quest should start (max players reached)
        if (count($players) >= $this->quest->story->max_players) {
            $startQuestEvent = new StartQuestEvent($this->player, $this->quest);
            $startQuestEvent->process();
        }
    }
}
