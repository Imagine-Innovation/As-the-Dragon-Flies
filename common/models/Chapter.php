<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "chapter".
 *
 * @property int $id Primary key
 * @property int $story_id Foreign key to “story” table
 * @property int $chapter_number Chapter number
 * @property string $name Chapter
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int|null $first_mission_id Optional foreign key to “mission” table to identify the first mission to complete
 *
 * @property Mission $firstMission
 * @property Mission[] $missions
 * @property Quest[] $quests
 * @property Story $story
 */
class Chapter extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chapter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'image', 'first_mission_id'], 'default', 'value' => null],
            [['chapter_number'], 'default', 'value' => 1],
            [['story_id', 'name'], 'required'],
            [['story_id', 'chapter_number', 'first_mission_id'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name', 'image'], 'string', 'max' => 64],
            [['story_id', 'chapter_number'], 'unique', 'targetAttribute' => ['story_id', 'chapter_number']],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['first_mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['first_mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'story_id' => 'Foreign key to “story” table',
            'chapter_number' => 'Chapter number',
            'name' => 'Chapter',
            'description' => 'Short description',
            'image' => 'Image',
            'first_mission_id' => 'Optional foreign key to “mission” table to identify the first mission to complete',
        ];
    }

    /**
     * Gets query for [[FirstMission]].
     *
     * @return \yii\db\ActiveQuery<Mission>
     */
    public function getFirstMission()
    {
        return $this->hasOne(Mission::class, ['id' => 'first_mission_id']);
    }

    /**
     * Gets query for [[Missions]].
     *
     * @return \yii\db\ActiveQuery<Mission>
     */
    public function getMissions()
    {
        return $this->hasMany(Mission::class, ['chapter_id' => 'id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuests()
    {
        return $this->hasMany(Quest::class, ['current_chapter_id' => 'id']);
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery<Story>
     */
    public function getStory()
    {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }
}
