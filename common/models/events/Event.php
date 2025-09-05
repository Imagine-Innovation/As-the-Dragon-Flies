<?php

namespace common\models\events;

use common\models\Notification;
use common\models\NotificationPlayer;
use common\models\Player;
use common\models\Quest;
use Yii;
use yii\base\BaseObject;

abstract class Event extends BaseObject
{

    public $sessionId;
    public $player;
    public $quest;
    public $timestamp;
    public $notificationId;

    public function __construct(string $sessionId, Player $player, Quest $quest, $config = []) {
        $this->notificationId = null;
        $this->sessionId = $sessionId;
        $this->player = $player;
        $this->quest = $quest;
        $this->timestamp = time();
        parent::__construct($config);
    }

    abstract public function getType(): string;

    abstract public function getPayload(): array;

    abstract public function getTitle(): string;

    abstract public function getMessage(): string;

    abstract public function process(): void;

    public function toArray(): array {
        $array = [
            'type' => $this->getType(),
            'notificationId' => $this->notificationId,
            'sessionId' => $this->sessionId,
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'timestamp' => $this->timestamp,
            'payload' => $this->getPayload()
        ];
        Yii::debug($array);
        return $array;
    }

    protected function broadcast() {
        $client = new \yii\httpclient\Client();
        $data = [
            'questId' => $this->quest->id,
            'message' => $this->toArray(),
            'excludeSessionId' => $this->sessionId,
        ];

        try {
            $response = $client->createRequest()
                    ->setMethod('POST')
                    ->setUrl('http://127.0.0.1:8083/broadcast')
                    ->setData($data)
                    ->setFormat(\yii\httpclient\Client::FORMAT_JSON)
                    ->send();

            if (!$response->isOk) {
                Yii::error("Failed to broadcast event. HTTP status: " . $response->getStatusCode());
                Yii::error("Response body: " . $response->getContent());
            } else {
                Yii::info("Successfully sent event to event server.", 'eventhandler');
            }
        } catch (\yii\httpclient\Exception $e) {
            Yii::error("Exception while trying to broadcast event: " . $e->getMessage());
        }
    }

    protected function savePlayerNotification($notificationId): void {
        // Create notification_player entries for all players in quest
        $players = $this->quest->currentPlayers;
        foreach ($players as $player) {
            if ($player->id != $this->player->id) {
                Yii::debug("*** Debug *** Event - savePlayerNotification - notificationId={$notificationId}, player->id={$player->id}");
                $notificationPlayer = new NotificationPlayer([
                    'notification_id' => $notificationId,
                    'player_id' => $player->id,
                    'is_read' => 0
                ]);
                $notificationPlayer->save();
            }
        }
    }

    protected function createNotification(): Notification {
        $record = ([
            'initiator_id' => $this->player->id,
            'quest_id' => $this->quest->id,
            'notification_type' => $this->getType(),
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'created_at' => time(),
            'payload' => json_encode($this->getPayload()),
            'is_private' => 0,
        ]);
        Yii::debug("*** debug *** Event - createNotification");
        $notification = new Notification($record);

        $this->notificationId = ($notification->save()) ? $notification->id : null;

        return $notification;
    }
}
