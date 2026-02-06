<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_spell".
 *
 * @property int $class_id Foreign key to “character_class” table
 * @property int $spell_id Foreign key to “spell” table
 *
 * @property CharacterClass $class
 * @property Spell $spell
 */
class ClassSpell extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'class_spell';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id', 'spell_id'], 'required'],
            [['class_id', 'spell_id'], 'integer'],
            [['class_id', 'spell_id'], 'unique', 'targetAttribute' => ['class_id', 'spell_id']],
            [
                ['class_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CharacterClass::class,
                'targetAttribute' => ['class_id' => 'id'],
            ],
            [
                ['spell_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Spell::class,
                'targetAttribute' => ['spell_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'class_id' => 'Foreign key to “character_class” table',
            'spell_id' => 'Foreign key to “spell” table',
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
     * Gets query for [[Spell]].
     *
     * @return \yii\db\ActiveQuery<Spell>
     */
    public function getSpell()
    {
        return $this->hasOne(Spell::class, ['id' => 'spell_id']);
    }
}
