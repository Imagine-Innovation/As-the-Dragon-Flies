<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "trap".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to "mission" table
 * @property int $damage_type_id Foreign key to "damage_type" table
 * @property string $name Trap
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property string $damage Damage dice
 * @property int $is_team_trap Indicates that the whole team is trapped
 *
 * @property DamageType $damageType
 * @property Mission $mission
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
            [['description', 'image'], 'default', 'value' => null],
            [['is_team_trap'], 'default', 'value' => 0],
            [['mission_id', 'damage_type_id', 'name', 'damage'], 'required'],
            [['mission_id', 'damage_type_id', 'is_team_trap'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 32],
            [['damage'], 'string', 'max' => 8],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to "mission" table',
            'damage_type_id' => 'Foreign key to "damage_type" table',
            'name' => 'Trap',
            'description' => 'Short description',
            'image' => 'Image',
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
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }
}
