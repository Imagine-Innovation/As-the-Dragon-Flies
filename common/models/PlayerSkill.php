<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_skill".
 *
 * @property int $player_id Foreign key to “player” table
 * @property int $skill_id Foreign key to “skill” table
 * @property int $is_proficient The player is proficient in the skill
 * @property int $bonus Skill bonus
 *
 * @property Player $player
 * @property Skill $skill
 */
class PlayerSkill extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['bonus'], 'default', 'value' => 0],
            [['player_id', 'skill_id'], 'required'],
            [['player_id', 'skill_id', 'is_proficient', 'bonus'], 'integer'],
            [['player_id', 'skill_id'], 'unique', 'targetAttribute' => ['player_id', 'skill_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to “player” table',
            'skill_id' => 'Foreign key to “skill” table',
            'is_proficient' => 'The player is proficient in the skill',
            'bonus' => 'Skill bonus',
        ];
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }

    /**
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery<Skill>
     */
    public function getSkill() {
        return $this->hasOne(Skill::class, ['id' => 'skill_id']);
    }
}
