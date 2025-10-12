<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "trap".
 *
 * @property int $id Primary key
 * @property int $decor_id
 * @property int $damage_type_id Foreign key to “damage_type” table
 * @property string $name Trap
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property string $damage Damage dice
 * @property int $is_team_trap Indicates that the whole team is trapped
 * @property int $found The percentage chance that the trap will be dedected
 *
 * @property Action[] $actions
 * @property DamageType $damageType
 * @property Decor $decor
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
            [['found'], 'default', 'value' => 25],
            [['decor_id', 'damage_type_id', 'name', 'damage'], 'required'],
            [['decor_id', 'damage_type_id', 'is_team_trap', 'found'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['damage'], 'string', 'max' => 8],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
            [['decor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Decor::class, 'targetAttribute' => ['decor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'decor_id' => 'Decor ID',
            'damage_type_id' => 'Foreign key to “damage_type” table',
            'name' => 'Trap',
            'description' => 'Short description',
            'image' => 'Image',
            'damage' => 'Damage dice',
            'is_team_trap' => 'Indicates that the whole team is trapped',
            'found' => 'The percentage chance that the trap will be dedected',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['trap_id' => 'id']);
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
     * Gets query for [[Decor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecor() {
        return $this->hasOne(Decor::class, ['id' => 'decor_id']);
    }

}
