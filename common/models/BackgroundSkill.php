<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "background_skill".
 *
 * @property int $background_id Foreign key to “background” table
 * @property int $skill_id Foreign key to “skill” table
 *
 * @property Background $background
 * @property Skill $skill
 */
class BackgroundSkill extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'background_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['background_id', 'skill_id'], 'required'],
            [['background_id', 'skill_id'], 'integer'],
            [['background_id', 'skill_id'], 'unique', 'targetAttribute' => ['background_id', 'skill_id']],
            [
                ['background_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Background::class,
                'targetAttribute' => ['background_id' => 'id'],
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
            'background_id' => 'Foreign key to “background” table',
            'skill_id' => 'Foreign key to “skill” table',
        ];
    }

    /**
     * Gets query for [[Background]].
     *
     * @return \yii\db\ActiveQuery<Background>
     */
    public function getBackground()
    {
        return $this->hasOne(Background::class, ['id' => 'background_id']);
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
