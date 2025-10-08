<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_session".
 *
 * @property string $id Primary key
 * @property int|null $quest_id Foreign key to “quest” table
 * @property int|null $player_id Foreign key to “player” table
 * @property string|null $client_id Connection ID (socket)
 * @property int $last_ts Last time the session was used
 *
 * @property Player $player
 * @property Quest $quest
 */
class QuestSession extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['quest_id', 'player_id', 'client_id'], 'default', 'value' => null],
            [['last_ts'], 'default', 'value' => time()],
            [['id'], 'required'],
            [['quest_id', 'player_id', 'last_ts'], 'integer'],
            [['id', 'client_id'], 'string', 'max' => 64],
            [['client_id'], 'unique'],
            [['id'], 'unique'],
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
            'quest_id' => 'Foreign key to “quest” table',
            'player_id' => 'Foreign key to “player” table',
            'client_id' => 'Connection ID (socket)',
            'last_ts' => 'Last time the session was used',
        ];
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
}
