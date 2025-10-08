<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ability_default".
 *
 * @property int $race_group_id Foreign key to “race_group” table
 * @property int $class_id Foreign key to “character_class” table
 * @property int $ability_id Foreign key to “ability” table
 * @property int $score Default score
 *
 * @property Ability $ability
 * @property CharacterClass $class
 * @property RaceGroup $raceGroup
 */
class AbilityDefault extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ability_default';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['race_group_id', 'class_id', 'ability_id', 'score'], 'required'],
            [['race_group_id', 'class_id', 'ability_id', 'score'], 'integer'],
            [['race_group_id', 'class_id', 'ability_id'], 'unique', 'targetAttribute' => ['race_group_id', 'class_id', 'ability_id']],
            [['race_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => RaceGroup::class, 'targetAttribute' => ['race_group_id' => 'id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'race_group_id' => 'Foreign key to “race_group” table',
            'class_id' => 'Foreign key to “character_class” table',
            'ability_id' => 'Foreign key to “ability” table',
            'score' => 'Default score',
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
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[RaceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroup() {
        return $this->hasOne(RaceGroup::class, ['id' => 'race_group_id']);
    }

}
