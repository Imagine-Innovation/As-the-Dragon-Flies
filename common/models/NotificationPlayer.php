<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notification_player".
 *
 * @property int $notification_id Foreign key to "notification" table
 * @property int $player_id Foreign key to "player" table
 * @property int $is_read Whether the player has read this notification
 * @property int|null $read_at When the player read this notification
 * @property int $is_dismissed Whether the player has dismissed this notification
 * @property int|null $dismissed_at When the player dismissed this notification
 *
 * @property Notification $notification
 * @property Player $player
 */
class NotificationPlayer extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'notification_player';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['notification_id', 'player_id'], 'required'],
            [['notification_id', 'player_id', 'is_read', 'read_at', 'is_dismissed', 'dismissed_at'], 'integer'],
            [['notification_id', 'player_id'], 'unique', 'targetAttribute' => ['notification_id', 'player_id']],
            [['notification_id'], 'exist', 'skipOnError' => true, 'targetClass' => Notification::class, 'targetAttribute' => ['notification_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'notification_id' => 'Foreign key to \"notification\" table',
            'player_id' => 'Foreign key to \"player\" table',
            'is_read' => 'Whether the player has read this notification',
            'read_at' => 'When the player read this notification',
            'is_dismissed' => 'Whether the player has dismissed this notification',
            'dismissed_at' => 'When the player dismissed this notification',
        ];
    }

    /**
     * Gets query for [[Notification]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotification() {
        return $this->hasOne(Notification::class, ['id' => 'notification_id']);
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }
}
