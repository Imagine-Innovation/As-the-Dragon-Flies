<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_class".
 *
 * @property int $story_id Foreign key to “story” table
 * @property int $class_id Foreign key to “character_class” table
 *
 * @property CharacterClass $class
 * @property Story $story
 */
class StoryClass extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story_class';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['story_id', 'class_id'], 'required'],
            [['story_id', 'class_id'], 'integer'],
            [['story_id', 'class_id'], 'unique', 'targetAttribute' => ['story_id', 'class_id']],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'story_id' => 'Foreign key to “story” table',
            'class_id' => 'Foreign key to “character_class” table',
        ];
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery<Story>
     */
    public function getStory() {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }
}
