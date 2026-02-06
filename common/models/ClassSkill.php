<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_skill".
 *
 * @property int $class_id Foreign key to “character_class” table
 * @property int $skill_id Foreign key to “skill” table
 *
 * @property CharacterClass $class
 * @property Skill $skill
 */
class ClassSkill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'class_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id', 'skill_id'], 'required'],
            [['class_id', 'skill_id'], 'integer'],
            [['class_id', 'skill_id'], 'unique', 'targetAttribute' => ['class_id', 'skill_id']],
            [
                ['class_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CharacterClass::class,
                'targetAttribute' => ['class_id' => 'id'],
            ],
            [
                ['skill_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Skill::class,
                'targetAttribute' => ['skill_id' => 'id'],
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
            'skill_id' => 'Foreign key to “skill” table',
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
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery<Skill>
     */
    public function getSkill()
    {
        return $this->hasOne(Skill::class, ['id' => 'skill_id']);
    }
}
