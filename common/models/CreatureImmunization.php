<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_immunization".
 *
 * @property int $creature_id Foreign key to “creature” table
 * @property int $condition_id Foreign key to “condition” table
 *
 * @property Condition $condition
 * @property Creature $creature
 */
class CreatureImmunization extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature_immunization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['creature_id', 'condition_id'], 'required'],
            [['creature_id', 'condition_id'], 'integer'],
            [['creature_id', 'condition_id'], 'unique', 'targetAttribute' => ['creature_id', 'condition_id']],
            [['creature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Creature::class, 'targetAttribute' => ['creature_id' => 'id']],
            [['condition_id'], 'exist', 'skipOnError' => true, 'targetClass' => Condition::class, 'targetAttribute' => ['condition_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'creature_id' => 'Foreign key to “creature” table',
            'condition_id' => 'Foreign key to “condition” table',
        ];
    }

    /**
     * Gets query for [[Condition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCondition() {
        return $this->hasOne(Condition::class, ['id' => 'condition_id']);
    }

    /**
     * Gets query for [[Creature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreature() {
        return $this->hasOne(Creature::class, ['id' => 'creature_id']);
    }

}
