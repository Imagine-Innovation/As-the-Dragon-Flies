<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "race_ability".
 *
 * @property int $race_id Foreign key to "race" table
 * @property int $ability_id Foreign key to "ability" table
 * @property int $bonus Ability bonus granted for a race. 0 means, that no bonus is granted.
 *
 * @property Ability $ability
 * @property Race $race
 */
class RaceAbility extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'race_ability';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['race_id', 'ability_id'], 'required'],
            [['race_id', 'ability_id', 'bonus'], 'integer'],
            [['race_id', 'ability_id'], 'unique', 'targetAttribute' => ['race_id', 'ability_id']],
            [['race_id'], 'exist', 'skipOnError' => true, 'targetClass' => Race::class, 'targetAttribute' => ['race_id' => 'id']],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'race_id' => 'Foreign key to "race" table',
            'ability_id' => 'Foreign key to "ability" table',
            'bonus' => 'Ability bonus granted for a race. 0 means, that no bonus is granted.',
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
     * Gets query for [[Race]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRace() {
        return $this->hasOne(Race::class, ['id' => 'race_id']);
    }
}
