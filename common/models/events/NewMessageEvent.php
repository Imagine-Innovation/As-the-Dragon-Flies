<?php

namespace common\models\events;

use common\models\NotificationPlayer;
use common\models\QuestChat;
use Yii;

class NewMessageEvent extends Event {

    public $message;

    public function __construct($player, $quest, $message, $config = []) {
        $this->message = $message;
        parent::__construct($player, $quest, $config);
    }

    public function getType(): string {
        return 'new-message';
    }

    public function getTitle(): string {
        return 'New message';
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getPayload(): array {
        return [
            'playerName' => $this->player->name,
            'playerId' => $this->player->id,
            'message' => $this->message,
            'timestamp' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        // Save message to quest_chat
        $questChat = new QuestChat([
            'quest_id' => $this->quest->id,
            'player_id' => $this->player->id,
            'message' => $this->message,
            'created_at' => time()
        ]);
        $questChat->save();

        // Create notification
        $notification = $this->createNotification();

        // Create notification_player entries for all players in quest
        $currentPlayers = $this->quest->currentPlayers;
        foreach ($currentPlayers as $player) {
            if ($player->id != $this->player->id) {
                Yii::debug("*** Debug *** NewMessageEvent - process - notification->id={$notification->id}, player->id={$player->id}");
                $notificationPlayer = new NotificationPlayer([
                    'notification_id' => $notification->id,
                    'player_id' => $player->id,
                    'is_read' => 0,
                    'is_dismissed' => 0
                ]);
                $notificationPlayer->save();
            }
        }

        // Broadcast event to all connected clients
        Yii::$app->eventHandler->broadcastToQuest(
                $this->quest->id,
                $this->toArray()
        );
    }
}
