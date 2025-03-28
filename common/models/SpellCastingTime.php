<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell_casting_time".
 *
 * @property int $id Primary key
 * @property string $name Spell casting time
 * @property int $duration Duration
 * @property string $unit Unit of duration
 * @property int $minutes Duration (minutes)
 * @property string|null $modifier Modifier
 *
 * @property Spell[] $spells
 */
class SpellCastingTime extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell_casting_time';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'duration', 'unit', 'minutes'], 'required'],
            [['duration', 'minutes'], 'integer'],
            [['name'], 'string', 'max' => 256],
            [['unit'], 'string', 'max' => 16],
            [['modifier'], 'string', 'max' => 128],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Spell casting time',
            'duration' => 'Duration',
            'unit' => 'Unit of duration',
            'minutes' => 'Duration (minutes)',
            'modifier' => 'Modifier',
        ];
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['casting_time_id' => 'id']);
    }
}
