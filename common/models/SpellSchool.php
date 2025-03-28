<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell_school".
 *
 * @property int $id Primary key
 * @property string $name Spell school
 * @property string|null $description Short description
 *
 * @property Spell[] $spells
 */
class SpellSchool extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell_school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Spell school',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['school_id' => 'id']);
    }
}
