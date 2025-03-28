<?php

namespace common\models;

use common\models\Quest;
use Yii;

/**
 * This is the model class for table "story".
 *
 * @property int $id Primary key
 * @property int|null $image_id Optional foreign key to "image" table
 * @property string $name Story title
 * @property string|null $description Short description
 * @property int $status Status of the story (9=draft, 10=published, 0=archived)
 * @property int $min_level Minimum level required to enter the story
 * @property int $max_level Maximum level required to enter the story
 * @property int $min_players Minimum number of players
 * @property int $max_players Maximum number of players
 *
 * @property CharacterClass[] $classes
 * @property EntryPoint[] $entryPoints
 * @property Image $image
 * @property Quest[] $quests
 * @property StoryClass[] $storyClasses
 * @property StoryIntro[] $storyIntros
 * @property StoryTag[] $storyTags
 * @property Tag[] $tags
 * @property Tile[] $tiles
 * 
 * @property Quest $tavern
 */
class Story extends \yii\db\ActiveRecord {

    const STATUS_DRAFT = 9;
    const STATUS_PUBLISHED = 10;
    const STATUS_ARCHIVED = 0;

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
            [['image_id', 'status', 'min_level', 'max_level', 'min_players', 'max_players'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
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
            'name' => 'Story title',
            'description' => 'Short description',
            'status' => 'Status of the story (9=draft, 10=published, 0=archived)',
            'min_level' => 'Minimum level required to enter the story',
            'max_level' => 'Maximum level required to enter the story',
            'min_players' => 'Minimum number of players',
            'max_players' => 'Maximum number of players',
        ];
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->via('storyClasses');
    }

    /**
     * Gets query for [[EntryPoints]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEntryPoints() {
        return $this->hasMany(EntryPoint::class, ['story_id' => 'id']);
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
     * Gets query for [[StoryIntros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoryIntros() {
        return $this->hasMany(StoryIntro::class, ['story_id' => 'id']);
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
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->via('storyTags');
    }

    /**
     * Gets query for [[Tiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTiles() {
        return $this->hasMany(Tile::class, ['id' => 'tile_id'])->via('entryPoints');
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
            'status' => Quest::STATUS_WAITING
        ]);
        return $quest;
    }
}
