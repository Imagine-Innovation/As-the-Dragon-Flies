<?php

namespace common\models\events;

use common\models\NotificationPlayer;
use Yii;

class StartQuestEvent extends Event {

    public function getType(): string {
        return 'start_quest';
    }

    public function getTitle(): string {
        return 'The quest has started';
    }

    public function getMessage(): string {
        return "{$this->quest->name} has started";
    }

    public function getPayload(): array {
        return [
            'questId' => $this->quest->id,
            'questName' => $this->quest->name,
            'startTime' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        // Update quest status
        $this->quest->status = 'playing';
        $this->quest->started_at = date('Y-m-d H:i:s', $this->timestamp);
        $this->quest->save();

        // Create notification
        $notification = $this->createNotification();

        // Create notification_player entries for all players in quest
        $questPlayers = $this->quest->getPlayers()->all();
        foreach ($questPlayers as $questPlayer) {
            $notificationPlayer = new NotificationPlayer();
            $notificationPlayer->notification_id = $notification->id;
            $notificationPlayer->player_id = $questPlayer->id;
            $notificationPlayer->is_read = 0;
            $notificationPlayer->save();
        }

        // Broadcast event to all connected clients
        Yii::$app->eventHandler->broadcastToQuest(
                $this->quest->id,
                $this->toArray()
        );
    }
}
