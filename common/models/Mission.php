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
 * @property Item[] $items
 * @property MissionItem[] $missionItems
 * @property MissionNpc[] $missionNpcs
 * @property MissionShape[] $missionShapes
 * @property Npc[] $npcs
 * @property Passage[] $passages
 * @property Shape[] $shapes
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
            'chapter_id' => 'Foreign key to "chapter" table',
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
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('mission_item', ['mission_id' => 'id']);
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
     * Gets query for [[MissionNpcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissionNpcs() {
        return $this->hasMany(MissionNpc::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[MissionShapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissionShapes() {
        return $this->hasMany(MissionShape::class, ['mission_id' => 'id']);
    }

    /**
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNpcs() {
        return $this->hasMany(Npc::class, ['id' => 'npc_id'])->viaTable('mission_npc', ['mission_id' => 'id']);
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
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->viaTable('mission_shape', ['mission_id' => 'id']);
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
