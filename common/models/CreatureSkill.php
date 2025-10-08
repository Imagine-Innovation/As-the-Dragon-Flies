<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_skill".
 *
 * @property int $creature_id Foreign key to “creature” table
 * @property int $skill_id Foreign key to “skill” table
 * @property int $score Score
 *
 * @property Creature $creature
 * @property Skill $skill
 */
class CreatureSkill extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['score'], 'default', 'value' => 0],
            [['creature_id', 'skill_id'], 'required'],
            [['creature_id', 'skill_id', 'score'], 'integer'],
            [['creature_id', 'skill_id'], 'unique', 'targetAttribute' => ['creature_id', 'skill_id']],
            [['creature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Creature::class, 'targetAttribute' => ['creature_id' => 'id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'creature_id' => 'Foreign key to “creature” table',
            'skill_id' => 'Foreign key to “skill” table',
            'score' => 'Score',
        ];
    }

    /**
     * Gets query for [[Creature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreature() {
        return $this->hasOne(Creature::class, ['id' => 'creature_id']);
    }

    /**
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkill() {
        return $this->hasOne(Skill::class, ['id' => 'skill_id']);
    }

}
