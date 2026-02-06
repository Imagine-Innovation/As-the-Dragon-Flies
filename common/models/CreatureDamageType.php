<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_damage_type".
 *
 * @property int $creature_id Foreign key to “creature” table
 * @property int $damage_type_id Foreign key to “damage_type” table
 * @property int $is_immune Indicates that the monster is immune to this damage type
 * @property int $is_resistant Indicates that the monster is resistant to this damage type
 * @property int $is_vulnerable Indicates that the monster is vulnerable to this damage type
 *
 * @property Creature $creature
 * @property DamageType $damageType
 */
class CreatureDamageType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'creature_damage_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_vulnerable'], 'default', 'value' => 0],
            [['creature_id', 'damage_type_id'], 'required'],
            [['creature_id', 'damage_type_id', 'is_immune', 'is_resistant', 'is_vulnerable'], 'integer'],
            [['creature_id', 'damage_type_id'], 'unique', 'targetAttribute' => ['creature_id', 'damage_type_id']],
            [
                ['creature_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Creature::class,
                'targetAttribute' => ['creature_id' => 'id'],
            ],
            [
                ['damage_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => DamageType::class,
                'targetAttribute' => ['damage_type_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'creature_id' => 'Foreign key to “creature” table',
            'damage_type_id' => 'Foreign key to “damage_type” table',
            'is_immune' => 'Indicates that the monster is immune to this damage type',
            'is_resistant' => 'Indicates that the monster is resistant to this damage type',
            'is_vulnerable' => 'Indicates that the monster is vulnerable to this damage type',
        ];
    }

    /**
     * Gets query for [[Creature]].
     *
     * @return \yii\db\ActiveQuery<Creature>
     */
    public function getCreature()
    {
        return $this->hasOne(Creature::class, ['id' => 'creature_id']);
    }

    /**
     * Gets query for [[DamageType]].
     *
     * @return \yii\db\ActiveQuery<DamageType>
     */
    public function getDamageType()
    {
        return $this->hasOne(DamageType::class, ['id' => 'damage_type_id']);
    }
}
