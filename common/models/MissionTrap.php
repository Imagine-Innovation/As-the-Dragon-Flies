<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission_trap".
 *
 * @property int $trap_id Foreign key to "trap" table
 * @property int $mission_id Foreign key to "mission" table
 * @property int $present Indicates the probability that the part is present in this sequence
 * @property int $fall Indicates the probability of the player falling into the trap
 *
 * @property Mission $mission
 * @property Trap $trap
 */
class MissionTrap extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'mission_trap';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['present'], 'default', 'value' => 25],
            [['fall'], 'default', 'value' => 50],
            [['trap_id', 'mission_id'], 'required'],
            [['trap_id', 'mission_id', 'present', 'fall'], 'integer'],
            [['trap_id', 'mission_id'], 'unique', 'targetAttribute' => ['trap_id', 'mission_id']],
            [['trap_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trap::class, 'targetAttribute' => ['trap_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'trap_id' => 'Foreign key to \"trap\" table',
            'mission_id' => 'Foreign key to \"mission\" table',
            'present' => 'Indicates the probability that the part is present in this sequence',
            'fall' => 'Indicates the probability of the player falling into the trap',
        ];
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[Trap]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrap() {
        return $this->hasOne(Trap::class, ['id' => 'trap_id']);
    }
}
