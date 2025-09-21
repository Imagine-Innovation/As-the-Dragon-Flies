<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_tag".
 *
 * @property int $story_id Foreign key to "story" table
 * @property int $tag_id Foreign key to "tag" table
 *
 * @property Story $story
 * @property Tag $tag
 */
class StoryTag extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['story_id', 'tag_id'], 'required'],
            [['story_id', 'tag_id'], 'integer'],
            [['story_id', 'tag_id'], 'unique', 'targetAttribute' => ['story_id', 'tag_id']],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::class, 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'story_id' => 'Foreign key to "story" table',
            'tag_id' => 'Foreign key to "tag" table',
        ];
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStory() {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }

    /**
     * Gets query for [[Tag]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTag() {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }
}
