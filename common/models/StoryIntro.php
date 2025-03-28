<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_intro".
 *
 * @property int $id Primary key
 * @property int|null $image_id Optional foreign key to "image" table
 * @property int $story_id Foreign key to "story" table
 * @property string $name Introduction title
 * @property string|null $description Short description
 *
 * @property Image $image
 * @property IntroAttribute[] $introAttributes
 * @property Story $story
 */
class StoryIntro extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story_intro';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['image_id', 'story_id'], 'integer'],
            [['story_id', 'name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'image_id' => 'Optional foreign key to \"image\" table',
            'story_id' => 'Foreign key to \"story\" table',
            'name' => 'Introduction title',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage() {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    /**
     * Gets query for [[IntroAttributes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIntroAttributes() {
        return $this->hasMany(IntroAttribute::class, ['intro_id' => 'id']);
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
