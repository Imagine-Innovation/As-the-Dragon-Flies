<?php

namespace common\models\events;

use common\helpers\SaveHelper;
use common\models\Notification;
use common\models\Player;
use common\models\Quest;
use Yii;
use yii\base\BaseObject;

abstract class Event extends BaseObject
{

    public Player $player;
    public Quest $quest;
    public int $timestamp;
    public ?int $notificationId = null;
    public string $language;

    /**
     *
     * @param Player $player
     * @param Quest $quest
     * @param array<string, mixed> $config
     */
    public function __construct(Player $player, Quest $quest, array $config = [])
    {
        $this->notificationId = null;
        $this->player = $player;
        $this->quest = $quest;
        $this->timestamp = time();
        parent::__construct($config);
        if (!isset($this->language)) {
            $this->language = Yii::$app->user->identity->language ?? Yii::$app->language ?? 'en';
        }
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
        $array = array_merge([
            'type' => $this->getType(),
            'notificationId' => $this->notificationId,
            'playerId' => $this->player->id,
            'questId' => $this->quest->id,
            'timestamp' => $this->timestamp,
                ], $this->getPayload());
        Yii::debug($array);
        return $array;
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
            'payload' => $this->getPayload(),
            'is_private' => 0,
        ];
        $notification = new Notification($notificationData);

        SaveHelper::save($notification);
        $this->notificationId = $notification->id;
        return $notification;
    }
}
