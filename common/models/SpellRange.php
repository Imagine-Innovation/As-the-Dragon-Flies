<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell_range".
 *
 * @property int $id Primary key
 * @property string $name Spell range
 * @property int $is_self Indicates that it applies to the spellcaster
 * @property int|null $range Range (ft.)
 * @property string|null $area_of_effect Area of effect
 * @property string|null $special Special feature
 *
 * @property Spell[] $spells
 */
class SpellRange extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell_range';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['range', 'area_of_effect', 'special'], 'default', 'value' => null],
            [['is_self'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['is_self', 'range'], 'integer'],
            [['name', 'area_of_effect', 'special'], 'string', 'max' => 32],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Spell range',
            'is_self' => 'Indicates that it applies to the spellcaster',
            'range' => 'Range (ft.)',
            'area_of_effect' => 'Area of effect',
            'special' => 'Special feature',
        ];
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['range_id' => 'id']);
    }

}
