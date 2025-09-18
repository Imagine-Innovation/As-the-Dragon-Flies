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
 * @property int $low_bound Low bound of probability that history will go in this direction
 * @property int $high_bound High bound of probability that history will go in this direction
 *
 * @property Chapter $chapter
 * @property Interaction[] $interactions
 * @property Item[] $items
 * @property MissionItem[] $missionItems
 * @property MissionNpc[] $missionNpcs
 * @property MissionShape[] $missionShapes
 * @property MissionTrap[] $missionTraps
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
            [['description'], 'default', 'value' => null],
            [['low_bound'], 'default', 'value' => 0],
            [['high_bound'], 'default', 'value' => 100],
            [['chapter_id', 'name'], 'required'],
            [['chapter_id', 'low_bound', 'high_bound'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
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
            'low_bound' => 'Low bound of probability that history will go in this direction',
            'high_bound' => 'High bound of probability that history will go in this direction',
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
     * Gets query for [[MissionTraps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissionTraps() {
        return $this->hasMany(MissionTrap::class, ['mission_id' => 'id']);
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
        return $this->hasMany(Trap::class, ['id' => 'trap_id'])->viaTable('mission_trap', ['mission_id' => 'id']);
    }

}
