<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_ability".
 *
 * @property int $class_id Foreign key to "class" table
 * @property int $ability_id Foreign key to "ability" table
 * @property int $is_primary_ability
 * @property int $is_saving_throw
 *
 * @property Ability $ability
 * @property Class $class
 */
class ClassAbility extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_ability';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['class_id', 'ability_id'], 'required'],
            [['class_id', 'ability_id', 'is_primary_ability', 'is_saving_throw'], 'integer'],
            [['class_id', 'ability_id'], 'unique', 'targetAttribute' => ['class_id', 'ability_id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'class_id' => 'Foreign key to \"class\" table',
            'ability_id' => 'Foreign key to \"ability\" table',
            'is_primary_ability' => 'Is Primary Ability',
            'is_saving_throw' => 'Is Saving Throw',
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
}
