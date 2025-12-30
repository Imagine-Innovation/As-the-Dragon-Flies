<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_player_condition".
 *
 * @property int $quest_id Foreign key to “quest” table
 * @property int $player_id Foreign key to “player” table
 * @property int $condition_id Foreign key to “condition” table
 * @property int|null $rounds_left Number of round left before the condition ends
 *
 * @property Condition $condition
 * @property QuestPlayer $quest
 */
class QuestPlayerCondition extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_player_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['rounds_left'], 'default', 'value' => null],
            [['quest_id', 'player_id', 'condition_id'], 'required'],
            [['quest_id', 'player_id', 'condition_id', 'rounds_left'], 'integer'],
            [['quest_id', 'player_id', 'condition_id'], 'unique', 'targetAttribute' => ['quest_id', 'player_id', 'condition_id']],
            [['quest_id', 'player_id'], 'exist', 'skipOnError' => true, 'targetClass' => QuestPlayer::class, 'targetAttribute' => ['quest_id' => 'quest_id', 'player_id' => 'player_id']],
            [['condition_id'], 'exist', 'skipOnError' => true, 'targetClass' => Condition::class, 'targetAttribute' => ['condition_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'quest_id' => 'Foreign key to “quest” table',
            'player_id' => 'Foreign key to “player” table',
            'condition_id' => 'Foreign key to “condition” table',
            'rounds_left' => 'Number of round left before the condition ends',
        ];
    }

    /**
     * Gets query for [[Condition]].
     *
     * @return \yii\db\ActiveQuery<Condition>
     */
    public function getCondition() {
        return $this->hasOne(Condition::class, ['id' => 'condition_id']);
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery<QuestPlayer>
     */
    public function getQuest() {
        return $this->hasOne(QuestPlayer::class, ['quest_id' => 'quest_id', 'player_id' => 'player_id']);
    }
}
