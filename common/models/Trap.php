<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "trap".
 *
 * @property int $id Primary key
 * @property int $damage_type_id Foreign key to "damage_type" table
 * @property string $name Trap
 * @property string $damage Damage dice
 * @property int $is_team_trap Indicates that the whole team is trapped
 *
 * @property DamageType $damageType
 * @property MissionTrap[] $missionTraps
 * @property Mission[] $missions
 */
class Trap extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'trap';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['is_team_trap'], 'default', 'value' => 0],
            [['damage_type_id', 'name', 'damage'], 'required'],
            [['damage_type_id', 'is_team_trap'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['damage'], 'string', 'max' => 8],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'damage_type_id' => 'Foreign key to \"damage_type\" table',
            'name' => 'Trap',
            'damage' => 'Damage dice',
            'is_team_trap' => 'Indicates that the whole team is trapped',
        ];
    }

    /**
     * Gets query for [[DamageType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDamageType() {
        return $this->hasOne(DamageType::class, ['id' => 'damage_type_id']);
    }

    /**
     * Gets query for [[MissionTraps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissionTraps() {
        return $this->hasMany(MissionTrap::class, ['trap_id' => 'id']);
    }

    /**
     * Gets query for [[Missions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissions() {
        return $this->hasMany(Mission::class, ['id' => 'mission_id'])->viaTable('mission_trap', ['trap_id' => 'id']);
    }

}
