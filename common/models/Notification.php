<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $id Primary key
 * @property int $player_id
 * @property int $quest_id Foreign key to "quest" table
 * @property string $notification_type Notifcation type
 * @property string $title Notification title
 * @property string $message Notification content
 * @property int|null $source_id ID of the source object (quest_id, player_id, etc.)
 * @property string|null $source_type Type of the source object (quest, player, etc.)
 * @property int $created_at When the notification was created
 * @property int|null $expires_at When the notification expires (optional)
 * @property int $is_private Notification is private
 *
 * @property NotificationPlayer[] $notificationPlayers
 * @property Player $player
 * @property Player[] $players
 * @property Quest $quest
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
            [['player_id', 'quest_id', 'notification_type', 'message'], 'required'],
            [['player_id', 'quest_id', 'source_id', 'created_at', 'expires_at', 'is_private'], 'integer'],
            [['message'], 'string'],
            [['notification_type', 'source_type'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 4096],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'player_id' => 'Player ID',
            'quest_id' => 'Foreign key to \"quest\" table',
            'notification_type' => 'Notifcation type',
            'title' => 'Notification title',
            'message' => 'Notification content',
            'source_id' => 'ID of the source object (quest_id, player_id, etc.)',
            'source_type' => 'Type of the source object (quest, player, etc.)',
            'created_at' => 'When the notification was created',
            'expires_at' => 'When the notification expires (optional)',
            'is_private' => 'Notification is private',
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
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
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
}
