<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property int $id Primary key
 * @property string $name Tag
 * @property string|null $description Short description
 *
 * @property Story[] $stories
 * @property StoryTag[] $storyTags
 */
class Tag extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
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
            'name' => 'Tag',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Stories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStories() {
        return $this->hasMany(Story::class, ['id' => 'story_id'])->viaTable('story_tag', ['tag_id' => 'id']);
    }

    /**
     * Gets query for [[StoryTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoryTags() {
        return $this->hasMany(StoryTag::class, ['tag_id' => 'id']);
    }

}
