<?php

namespace common\models;

use common\components\AppStatus;
use common\models\Quest;
use Yii;

/**
 * This is the model class for table "story".
 *
 * @property int $id Primary key
 * @property string $name Story title
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int $status Status of the story (200=draft, 201=published, 202=archived)
 * @property int $min_level Minimum level required to enter the story
 * @property int $max_level Maximum level required to enter the story
 * @property int $min_players Minimum number of players
 * @property int $max_players Maximum number of players
 * @property string $language Language
 *
 * @property Chapter[] $chapters
 * @property CharacterClass[] $classes
 * @property Quest[] $quests
 * @property StoryClass[] $storyClasses
 * @property StoryTag[] $storyTags
 * @property Tag[] $tags
 *
 * ************ Custom properties
 *
 * @property Quest $tavern
 * @property sting $requestedLevels
 * @property sting $companySize
 */
class Story extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['status'], 'default', 'value' => AppStatus::DRAFT->value],
            [['min_players'], 'default', 'value' => 1],
            [['max_players'], 'default', 'value' => 4],
            [['language'], 'default', 'value' => 'en'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['status', 'min_level', 'max_level', 'min_players', 'max_players'], 'integer'],
            [['status'], 'in', 'range' => AppStatus::getValuesForStory()],
            [['name', 'image'], 'string', 'max' => 32],
            [['language'], 'string', 'max' => 8],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Story title',
            'description' => 'Short description',
            'image' => 'Image',
            'status' => 'Status of the story (200=draft, 201=published, 202=archived)',
            'min_level' => 'Minimum level required to enter the story',
            'max_level' => 'Maximum level required to enter the story',
            'min_players' => 'Minimum number of players',
            'max_players' => 'Maximum number of players',
            'language' => 'Language',
        ];
    }

    /**
     * Gets query for [[Chapters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapters() {
        return $this->hasMany(Chapter::class, ['story_id' => 'id'])
                        ->orderBy(['chapter_number' => SORT_ASC]);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->viaTable('story_class', ['story_id' => 'id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuests() {
        return $this->hasMany(Quest::class, ['story_id' => 'id']);
    }

    /**
     * Gets query for [[StoryClasses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoryClasses() {
        return $this->hasMany(StoryClass::class, ['story_id' => 'id']);
    }

    /**
     * Gets query for [[StoryTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoryTags() {
        return $this->hasMany(StoryTag::class, ['story_id' => 'id']);
    }

    /**
     * Gets query for [[Tags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags() {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->viaTable('story_tag', ['story_id' => 'id']);
    }

    /*     * *********************************
     *       Custom properties
     *       ********************************* */

    /**
     * Gets query for [[Tavern]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTavern() {
        $quest = Quest::findOne([
            'story_id' => $this->id,
            'status' => AppStatus::WAITING->value
        ]);
        return $quest;
    }

    /**
     * Generate a string that describes the number of requested player levels
     *
     * @return string
     */
    public function getRequiredLevels(): string {
        $max = $this->max_level ?? 0;
        $min = $this->min_level ?? 0;
        if (($min + $max) === 0) {
            return "Undefined";
        }
        if ($max === 1) {
            return "Beginner only";
        }
        if ($max === $min) {
            return "Level {$min} only";
        }
        if (($max - $min) === 1) {
            return "Level {$min} or {$max}";
        }
        return "From level {$min} to level {$max}";
    }

    /**
     * Generate a string that describes the number of expected players
     *
     * @return string
     */
    public function getCompanySize(): string {
        $max = $this->max_players ?? 0;
        $min = $this->min_players ?? 0;
        if (($min + $max) === 0) {
            return "Undefined";
        }
        if ($max === 1) {
            return "Single player";
        }
        if (($max - $min) === 0) {
            return "{$min} players";
        }
        if (($max - $min) === 1) {
            return "{$min} or {$max} players";
        }
        return "{$min} to {$max} players";
    }
}
