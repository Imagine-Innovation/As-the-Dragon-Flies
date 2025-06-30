<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id Primary key
 * @property int $initiator_id Foreign key to "player" table. Identifies the initiator of the notification
 * @property int|null $quest_id Foreign key to "quest" table
 * @property string $notification_type Notifcation type
 * @property string $title Notification title
 * @property string $message Notification content
 * @property int $created_at When the notification was created
 * @property int|null $expires_at When the notification expires (optional)
 * @property int $is_private Notification is private
 * @property string|null $payload Notification Payload
 *
 * @property Player $initiator
 * @property NotificationPlayer[] $notificationPlayers
 * @property Player[] $players
 * @property Quest $quest
 */
class Notification extends \yii\db\ActiveRecord
{


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
            [['quest_id', 'expires_at', 'payload'], 'default', 'value' => null],
            [['title'], 'default', 'value' => 'Technical event'],
            [['is_private'], 'default', 'value' => 0],
            [['initiator_id', 'notification_type', 'message'], 'required'],
            [['initiator_id', 'quest_id', 'created_at', 'expires_at', 'is_private'], 'integer'],
            [['message'], 'string'],
            [['payload'], 'safe'],
            [['notification_type'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 4096],
            [['initiator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['initiator_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'initiator_id' => 'Foreign key to \"player\" table. Identifies the initiator of the notification',
            'quest_id' => 'Foreign key to \"quest\" table',
            'notification_type' => 'Notifcation type',
            'title' => 'Notification title',
            'message' => 'Notification content',
            'created_at' => 'When the notification was created',
            'expires_at' => 'When the notification expires (optional)',
            'is_private' => 'Notification is private',
            'payload' => 'Notification Payload',
        ];
    }

    /**
     * Gets query for [[Initiator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInitiator() {
        return $this->hasOne(Player::class, ['id' => 'initiator_id']);
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
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('notification_player', ['notification_id' => 'id']);
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

}
