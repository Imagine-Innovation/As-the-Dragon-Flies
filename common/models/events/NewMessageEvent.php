<?php

namespace common\models\events;

use common\models\NotificationPlayer;
use common\models\QuestChat;
use common\models\Quest;
use common\models\Player;
use frontend\components\QuestMessages;
use Yii;

class NewMessageEvent extends Event {

    public $message;

    public function __construct(string $sessionId, Player $player, Quest $quest, string $message, array $config = []) {
        $this->message = $message;
        parent::__construct($sessionId, $player, $quest, $config);
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
        /*
          return [
          'playerName' => $this->player->name,
          'playerId' => $this->player->id,
          'avatar' => $this->player->image->file_name,
          'message' => $this->message,
          'timestamp' => date('Y-m-d H:i:s', $this->timestamp),
          'roundedTime' => floor($this->timestamp / 60) * 60 // rounded to the same minute
          ];
         *
         */
        return QuestMessages::payload($this->player, $this->message);
    }

    public function process(): void {
        /*
          // Save message to quest_chat
          $questChat = new QuestChat([
          'quest_id' => $this->quest->id,
          'player_id' => $this->player->id,
          'message' => $this->message,
          'created_at' => time()
          ]);
          $questChat->save();
         *
         */

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

        $this->broadcast();
    }
}
