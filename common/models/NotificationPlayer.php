<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notification_player".
 *
 * @property int $notification_id Foreign key to "notification" table
 * @property int $player_id Foreign key to "player" table
 * @property int $distributed_at Notification was distributed at
 * @property int $is_read Notification is read
 * @property int|null $read_at Notification was read at
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
            [['notification_id', 'player_id', 'distributed_at', 'is_read', 'read_at'], 'integer'],
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
            'distributed_at' => 'Notification was distributed at',
            'is_read' => 'Notification is read',
            'read_at' => 'Notification was read at',
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
