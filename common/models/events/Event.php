<?php

namespace common\models\events;

use common\helpers\SaveHelper;
use common\models\Notification;
use common\models\NotificationPlayer;
use common\models\Player;
use common\models\Quest;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

abstract class Event extends BaseObject
{

    public string $sessionId;
    public Player $player;
    public Quest $quest;
    public int $timestamp;
    public ?int $notificationId = null;

    /**
     *
     * @param string $sessionId
     * @param Player $player
     * @param Quest $quest
     * @param array<string, mixed> $config
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, array $config = [])
    {
        $this->notificationId = null;
        $this->sessionId = $sessionId;
        $this->player = $player;
        $this->quest = $quest;
        $this->timestamp = time();
        parent::__construct($config);
    }

    /**
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     *
     * @return array<string, mixed>
     */
    abstract public function getPayload(): array;

    /**
     *
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     *
     * @return string
     */
    abstract public function getMessage(): string;

    /**
     *
     * @return void
     */
    abstract public function process(): void;

    /**
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [
            'type' => $this->getType(),
            'notificationId' => $this->notificationId,
            'sessionId' => $this->sessionId,
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'timestamp' => $this->timestamp,
            'payload' => $this->getPayload(),
        ];
        Yii::debug($array);
        return $array;
    }

    /**
     *
     * @return array<string, mixed>
     */
    private function requestData(): array
    {
        return [
            'questId' => $this->quest->id,
            'message' => $this->toArray(),
            'excludeSessionId' => $this->sessionId,
        ];
    }

    /**
     *
     * @return void
     */
    protected function broadcast(): void
    {
        $client = new \yii\httpclient\Client();
        $data = $this->requestData();
        try {
            $response = $client
                    ->createRequest()
                    ->setMethod('POST')
                    ->setUrl('http://127.0.0.1:8083/broadcast')
                    ->setData($data)
                    ->setFormat(\yii\httpclient\Client::FORMAT_JSON)
                    ->send();

            if ($response->isOk) {
                Yii::info('Successfully sent event to event server.', 'eventhandler');
            } else {
                Yii::error('Failed to broadcast event. HTTP status: ' . $response->getStatusCode());
                Yii::error('Response body: ' . $response->getContent());
            }
        } catch (\yii\httpclient\Exception $e) {
            Yii::error('Exception while trying to broadcast event: ' . $e->getMessage());
        }
    }

    /**
     *
     * @param int $notificationId
     * @param int $playerId
     * @return void
     */
    protected function newPlayerNotification(int $notificationId, int $playerId): void
    {
        Yii::debug(
                "*** Debug *** Event - newPlayerNotification - notificationId={$notificationId}, player->id={$playerId}",
        );
        $notificationPlayer = new NotificationPlayer([
            'notification_id' => $notificationId,
            'player_id' => $playerId,
            'is_read' => 0,
        ]);

        SaveHelper::save($notificationPlayer);
    }

    /**
     *
     * @param int $notificationId
     * @return void
     */
    protected function savePlayerNotifications(int $notificationId): void
    {
        // Create notification_player entries for all players in quest
        $players = $this->quest->currentPlayers;
        foreach ($players as $player) {
            if ($player->id !== $this->player->id) {
                $this->newPlayerNotification($notificationId, $player->id);
            }
        }
    }

    /**
     *
     * @return Notification
     */
    protected function createNotification(): Notification
    {
        $notificationData = [
            'initiator_id' => $this->player->id,
            'quest_id' => $this->quest->id,
            'notification_type' => $this->getType(),
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'created_at' => time(),
            'payload' => json_encode($this->getPayload()),
            'is_private' => 0,
        ];
        $notification = new Notification($notificationData);

        SaveHelper::save($notification);
        $this->notificationId = $notification->id;
        return $notification;
    }
}
