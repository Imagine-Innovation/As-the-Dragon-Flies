<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_saving_throw".
 *
 * @property int $creature_id Foreign key to “creature” table
 * @property int $ability_id Foreign key to “ability” table
 * @property int $modifier Modifier to apply
 *
 * @property Ability $ability
 * @property Creature $creature
 */
class CreatureSavingThrow extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature_saving_throw';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['modifier'], 'default', 'value' => 0],
            [['creature_id', 'ability_id'], 'required'],
            [['creature_id', 'ability_id', 'modifier'], 'integer'],
            [['creature_id', 'ability_id'], 'unique', 'targetAttribute' => ['creature_id', 'ability_id']],
            [['creature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Creature::class, 'targetAttribute' => ['creature_id' => 'id']],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'creature_id' => 'Foreign key to “creature” table',
            'ability_id' => 'Foreign key to “ability” table',
            'modifier' => 'Modifier to apply',
        ];
    }

    /**
     * Gets query for [[Ability]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbility() {
        return $this->hasOne(Ability::class, ['id' => 'ability_id']);
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
