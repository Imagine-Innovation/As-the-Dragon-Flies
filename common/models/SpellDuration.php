<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell_duration".
 *
 * @property int $id Primary key
 * @property string $name Label of the duration (e.g. “Concentration, up to 1 day”)
 * @property int $duration Numeric value of the duration
 * @property string $unit Unit of the duration (minute, hour, day or round)
 * @property int $minutes Convertion of duration in minutes scale
 * @property int $is_exact Indicates that it is an exact duration. If false, means that it is “up to”
 * @property int $is_concentration Indicated that is needs concentration
 *
 * @property Spell[] $spells
 */
class SpellDuration extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell_duration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['is_concentration'], 'default', 'value' => 0],
            [['name', 'duration', 'unit', 'minutes'], 'required'],
            [['duration', 'minutes', 'is_exact', 'is_concentration'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['unit'], 'string', 'max' => 16],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Label of the duration (e.g. “Concentration, up to 1 day”)',
            'duration' => 'Numeric value of the duration',
            'unit' => 'Unit of the duration (minute, hour, day or round)',
            'minutes' => 'Convertion of duration in minutes scale',
            'is_exact' => 'Indicates that it is an exact duration. If false, means that it is “up to”',
            'is_concentration' => 'Indicated that is needs concentration',
        ];
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery<Spell>
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['duration_id' => 'id']);
    }
}
