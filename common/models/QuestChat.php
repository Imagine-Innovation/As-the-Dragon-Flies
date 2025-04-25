<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_chat".
 *
 * @property int $id Primary key
 * @property int $player_id
 * @property int $quest_id Foreign key to "quest" table
 * @property string $message Message content
 * @property int $created_at Created at
 *
 * @property Quest $quest
 * @property Player $sender
 */
class QuestChat extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['player_id', 'quest_id', 'message', 'created_at'], 'required'],
            [['player_id', 'quest_id', 'created_at'], 'integer'],
            [['message'], 'string'],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'player_id' => 'Sender ID',
            'quest_id' => 'Foreign key to \"quest\" table',
            'message' => 'Message content',
            'created_at' => 'Created at',
        ];
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
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }
}
