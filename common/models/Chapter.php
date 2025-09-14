<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chapter".
 *
 * @property int $id Primary key
 * @property int $story_id Foreign key to "story" table
 * @property string $name Chapter
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int $sort_order Chapter number
 *
 * @property Step[] $steps
 * @property Story $story
 */
class Chapter extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'chapter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['sort_order'], 'default', 'value' => 1],
            [['story_id', 'name'], 'required'],
            [['story_id', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 32],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'story_id' => 'Foreign key to \"story\" table',
            'name' => 'Chapter',
            'description' => 'Short description',
            'image' => 'Image',
            'sort_order' => 'Chapter number',
        ];
    }

    /**
     * Gets query for [[Steps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSteps() {
        return $this->hasMany(Step::class, ['chapter_id' => 'id']);
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStory() {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }

}
