<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to "mission" table
 * @property string $name Passage
 * @property string|null $description Short description
 * @property string $passage_type Passage type
 * @property int $status Status code. "0" for Opened, "1" for Half-opened, "2" for Closed and "3" for Locked
 * @property string|null $image Image
 * @property int $found Give the probability of finding the passage (%)
 *
 * @property Item[] $items
 * @property Mission $mission
 * @property PassageItem[] $passageItems
 * @property PassageSkill[] $passageSkills
 * @property Skill[] $skills
 */
class Passage extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['passage_type'], 'default', 'value' => 'D'],
            [['status'], 'default', 'value' => 0],
            [['found'], 'default', 'value' => 25],
            [['mission_id', 'name'], 'required'],
            [['mission_id', 'status', 'found'], 'integer'],
            [['description'], 'string'],
            [['name', 'passage_type', 'image'], 'string', 'max' => 32],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to \"mission\" table',
            'name' => 'Passage',
            'description' => 'Short description',
            'passage_type' => 'Passage type',
            'status' => 'Status code. \"0\" for Opened, \"1\" for Half-opened, \"2\" for Closed and \"3\" for Locked',
            'image' => 'Image',
            'found' => 'Give the probability of finding the passage (%)',
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('passage_item', ['passage_id' => 'id']);
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[PassageItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageItems() {
        return $this->hasMany(PassageItem::class, ['passage_id' => 'id']);
    }

    /**
     * Gets query for [[PassageSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageSkills() {
        return $this->hasMany(PassageSkill::class, ['passage_id' => 'id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->viaTable('passage_skill', ['passage_id' => 'id']);
    }

}
