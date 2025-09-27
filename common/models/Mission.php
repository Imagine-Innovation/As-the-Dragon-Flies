<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission".
 *
 * @property int $id Primary key
 * @property int $chapter_id Foreign key to "chapter" table
 * @property string $name Mission name
 * @property string|null $description Short description
 * @property string|null $image Image
 *
 * @property Chapter $chapter
 * @property Interaction[] $interactions
 * @property MissionItem[] $missionItems
 * @property Monster[] $monsters
 * @property Npc[] $npcs
 * @property Passage[] $passages
 * @property Success[] $successes
 * @property Trap[] $traps
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
            [['name', 'image'], 'string', 'max' => 32],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'chapter_id' => 'Foreign key to \"chapter\" table',
            'name' => 'Mission name',
            'description' => 'Short description',
            'image' => 'Image',
        ];
    }

    /**
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapter() {
        return $this->hasOne(Chapter::class, ['id' => 'chapter_id']);
    }

    /**
     * Gets query for [[Interactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInteractions() {
        return $this->hasMany(Interaction::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[MissionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissionItems() {
        return $this->hasMany(MissionItem::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Monsters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMonsters() {
        return $this->hasMany(Monster::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNpcs() {
        return $this->hasMany(Npc::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Passages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages() {
        return $this->hasMany(Passage::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Successes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuccesses() {
        return $this->hasMany(Success::class, ['next_mission_id' => 'id']);
    }

    /**
     * Gets query for [[Traps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraps() {
        return $this->hasMany(Trap::class, ['mission_id' => 'id']);
    }
}
