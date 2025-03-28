<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id Primary key
 * @property int $quest_id Foreign key to "quest" table
 * @property int $sender_id Foreign key to "player" table
 * @property int $created_at Created at
 * @property string $notification_type Notifcation type
 * @property int|null $data_id Foreign key to a contextual table depending on the notification type
 * @property string $message Message
 * @property int $acknowledged Notification is acknowledged by the quest
 * @property int $is_broadcast Notification is broadcasted
 *
 * @property NotificationPlayer[] $notificationPlayers
 * @property Player[] $players
 * @property Quest $quest
 * @property Player $sender
 */
class Notification extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['quest_id', 'sender_id'], 'required'],
            [['quest_id', 'sender_id', 'created_at', 'data_id', 'acknowledged', 'is_broadcast'], 'integer'],
            [['notification_type'], 'string', 'max' => 32],
            [['message'], 'string', 'max' => 4096],
            [['sender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['sender_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'quest_id' => 'Foreign key to \"quest\" table',
            'sender_id' => 'Foreign key to \"player\" table',
            'created_at' => 'Created at',
            'notification_type' => 'Notifcation type',
            'data_id' => 'Foreign key to a contextual table depending on the notification type',
            'message' => 'Message',
            'acknowledged' => 'Notification is acknowledged by the quest',
            'is_broadcast' => 'Notification is broadcasted',
        ];
    }

    /**
     * Gets query for [[NotificationPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationPlayers() {
        return $this->hasMany(NotificationPlayer::class, ['notification_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('notificationPlayers');
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[Sender]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSender() {
        return $this->hasOne(Player::class, ['id' => 'sender_id']);
    }
}
