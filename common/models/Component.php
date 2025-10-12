<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "component".
 *
 * @property int $id Primary key
 * @property string|null $code Code
 * @property string $name Component
 * @property string|null $description Short description
 *
 * @property SpellComponent[] $spellComponents
 * @property Spell[] $spells
 */
class Component extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'component';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['code', 'description'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'code' => 'Code',
            'name' => 'Component',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[SpellComponents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpellComponents() {
        return $this->hasMany(SpellComponent::class, ['component_id' => 'id']);
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['id' => 'spell_id'])->viaTable('spell_component', ['component_id' => 'id']);
    }

}
