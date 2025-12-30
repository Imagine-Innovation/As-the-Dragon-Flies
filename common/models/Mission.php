<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission".
 *
 * @property int $id Primary key
 * @property int $chapter_id Foreign key to “chapter” table
 * @property string $name Mission name
 * @property string|null $description Short description
 * @property string|null $image Image
 *
 * @property Action[] $actions
 * @property Chapter $chapter
 * @property Chapter[] $chapters
 * @property Decor[] $decors
 * @property Monster[] $monsters
 * @property Npc[] $npcs
 * @property Outcome[] $outcomes
 * @property Passage[] $passages
 * @property QuestProgress[] $questProgresses
 */
class Mission extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'mission';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['chapter_id', 'name'], 'required'],
            [['chapter_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'chapter_id' => 'Foreign key to “chapter” table',
            'name' => 'Mission name',
            'description' => 'Short description',
            'image' => 'Image',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery<Chapter>
     */
    public function getChapter() {
        return $this->hasOne(Chapter::class, ['id' => 'chapter_id']);
    }

    /**
     * Gets query for [[Chapters]].
     *
     * @return \yii\db\ActiveQuery<Chapter>
     */
    public function getChapters() {
        return $this->hasMany(Chapter::class, ['first_mission_id' => 'id']);
    }

    /**
     * Gets query for [[Decors]].
     *
     * @return \yii\db\ActiveQuery<Decor>
     */
    public function getDecors() {
        return $this->hasMany(Decor::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Monsters]].
     *
     * @return \yii\db\ActiveQuery<Monster>
     */
    public function getMonsters() {
        return $this->hasMany(Monster::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery<Npc>
     */
    public function getNpcs() {
        return $this->hasMany(Npc::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Outcomes]].
     *
     * @return \yii\db\ActiveQuery<Outcome>
     */
    public function getOutcomes() {
        return $this->hasMany(Outcome::class, ['next_mission_id' => 'id']);
    }

    /**
     * Gets query for [[Passages]].
     *
     * @return \yii\db\ActiveQuery<Passage>
     */
    public function getPassages() {
        return $this->hasMany(Passage::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[QuestProgresses]].
     *
     * @return \yii\db\ActiveQuery<QuestProgress>
     */
    public function getQuestProgresses() {
        return $this->hasMany(QuestProgress::class, ['mission_id' => 'id']);
    }
}
