<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_log".
 *
 * @property int $user_id Foreign key to "user" table
 * @property int|null $access_right_id Foreign key to "access_right" table
 * @property int|null $player_id Optional foreign key to "player" table
 * @property int|null $quest_id Optional foreign key to "quest" table
 * @property int|null $action_at Action was triggerd at
 * @property string|null $ip_address IP Address
 * @property int $denied Is action denied
 * @property string|null $reason Reason why
 *
 * @property AccessRight $accessRight
 * @property Player $player
 * @property Quest $quest
 * @property User $user
 */
class UserLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'user_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['access_right_id', 'player_id', 'quest_id', 'action_at', 'ip_address', 'reason'], 'default', 'value' => null],
            [['denied'], 'default', 'value' => 0],
            [['user_id'], 'required'],
            [['user_id', 'access_right_id', 'player_id', 'quest_id', 'action_at', 'denied'], 'integer'],
            [['ip_address'], 'string', 'max' => 64],
            [['reason'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['access_right_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessRight::class, 'targetAttribute' => ['access_right_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'user_id' => 'Foreign key to "user" table',
            'access_right_id' => 'Foreign key to "access_right" table',
            'player_id' => 'Optional foreign key to "player" table',
            'quest_id' => 'Optional foreign key to "quest" table',
            'action_at' => 'Action was triggerd at',
            'ip_address' => 'IP Address',
            'denied' => 'Is action denied',
            'reason' => 'Reason why',
        ];
    }

    /**
     * Gets query for [[AccessRight]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessRight() {
        return $this->hasOne(AccessRight::class, ['id' => 'access_right_id']);
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
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
