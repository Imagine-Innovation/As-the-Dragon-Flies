<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_condition".
 *
 * @property int $player_id Foreign key to “player” table
 * @property int $condition_id Foreign key to “creature_condition” table
 * @property int $rounds_left Number of round left before the condition ends
 *
 * @property CreatureCondition $condition
 * @property Player $player
 */
class PlayerCondition extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['rounds_left'], 'default', 'value' => 0],
            [['player_id', 'condition_id'], 'required'],
            [['player_id', 'condition_id', 'rounds_left'], 'integer'],
            [['player_id', 'condition_id'], 'unique', 'targetAttribute' => ['player_id', 'condition_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['condition_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreatureCondition::class, 'targetAttribute' => ['condition_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to “player” table',
            'condition_id' => 'Foreign key to “creature_condition” table',
            'rounds_left' => 'Number of round left before the condition ends',
        ];
    }

    /**
     * Gets query for [[Condition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCondition() {
        return $this->hasOne(CreatureCondition::class, ['id' => 'condition_id']);
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
