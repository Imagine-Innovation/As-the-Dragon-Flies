<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_proficiency".
 *
 * @property int $id Primary key
 * @property int $class_id Foreign key to “character_class” table
 * @property int $proficiency_id Foreign key to “proficiency” table
 * @property int $level_id Foreign key to “level” table
 * @property int $sort_order Sort order
 * @property int|null $bonus Proficiency bonus
 * @property string|null $dice Dice to roll to determine the proficiency bonus for Martial Arts ans Sneak Attack
 * @property int|null $spell_slot Spell slot
 * @property int|null $spell_level Spell level
 *
 * @property CharacterClass $class
 * @property Level $level
 * @property Proficiency $proficiency
 */
class ClassProficiency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'class_proficiency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bonus', 'dice', 'spell_slot', 'spell_level'], 'default', 'value' => null],
            [['class_id', 'proficiency_id', 'level_id', 'sort_order'], 'required'],
            [['class_id', 'proficiency_id', 'level_id', 'sort_order', 'bonus', 'spell_slot', 'spell_level'], 'integer'],
            [['dice'], 'string', 'max' => 8],
            [
                ['class_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CharacterClass::class,
                'targetAttribute' => ['class_id' => 'id'],
            ],
            [
                ['level_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Level::class,
                'targetAttribute' => ['level_id' => 'id'],
            ],
            [
                ['proficiency_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Proficiency::class,
                'targetAttribute' => ['proficiency_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'class_id' => 'Foreign key to “character_class” table',
            'proficiency_id' => 'Foreign key to “proficiency” table',
            'level_id' => 'Foreign key to “level” table',
            'sort_order' => 'Sort order',
            'bonus' => 'Proficiency bonus',
            'dice' => 'Dice to roll to determine the proficiency bonus for Martial Arts ans Sneak Attack',
            'spell_slot' => 'Spell slot',
            'spell_level' => 'Spell level',
        ];
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClass()
    {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery<Level>
     */
    public function getLevel()
    {
        return $this->hasOne(Level::class, ['id' => 'level_id']);
    }

    /**
     * Gets query for [[Proficiency]].
     *
     * @return \yii\db\ActiveQuery<Proficiency>
     */
    public function getProficiency()
    {
        return $this->hasOne(Proficiency::class, ['id' => 'proficiency_id']);
    }
}
