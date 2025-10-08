<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_alignment".
 *
 * @property int $creature_id Foreign key to “creature” table
 * @property int $alignment_id Foreign key to “alignment” table
 * @property int $random Sets the max threshold (between 1 and 100) to determine the monster's alignment.
 *
 * @property Alignment $alignment
 * @property Creature $creature
 */
class CreatureAlignment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature_alignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['random'], 'default', 'value' => 100],
            [['creature_id', 'alignment_id'], 'required'],
            [['creature_id', 'alignment_id', 'random'], 'integer'],
            [['creature_id', 'alignment_id'], 'unique', 'targetAttribute' => ['creature_id', 'alignment_id']],
            [['creature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Creature::class, 'targetAttribute' => ['creature_id' => 'id']],
            [['alignment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Alignment::class, 'targetAttribute' => ['alignment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'creature_id' => 'Foreign key to “creature” table',
            'alignment_id' => 'Foreign key to “alignment” table',
            'random' => 'Sets the max threshold (between 1 and 100) to determine the monster\'s alignment.',
        ];
    }

    /**
     * Gets query for [[Alignment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlignment() {
        return $this->hasOne(Alignment::class, ['id' => 'alignment_id']);
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
