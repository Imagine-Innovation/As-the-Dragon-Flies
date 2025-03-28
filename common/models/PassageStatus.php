<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage_status".
 *
 * @property int $id Primary key
 * @property string $name Status
 * @property string $code Status code. "O" for Opened, "C" for Closed, "H" for Half-opened and "L" for Locked
 * @property string|null $description Short description
 *
 * @property Item[] $items
 * @property PassageStatusItem[] $passageStatusItems
 * @property PassageStatusSkill[] $passageStatusSkills
 * @property Passage[] $passages
 * @property Skill[] $skills
 */
class PassageStatus extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['code', 'description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Status',
            'code' => 'Status code. \"O\" for Opened, \"C\" for Closed, \"H\" for Half-opened and \"L\" for Locked',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('passageStatusItems');
    }

    /**
     * Gets query for [[PassageStatusItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageStatusItems() {
        return $this->hasMany(PassageStatusItem::class, ['status_id' => 'id']);
    }

    /**
     * Gets query for [[PassageStatusSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageStatusSkills() {
        return $this->hasMany(PassageStatusSkill::class, ['status_id' => 'id']);
    }

    /**
     * Gets query for [[Passages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages() {
        return $this->hasMany(Passage::class, ['status_id' => 'id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->via('passageStatusSkills');
    }
}
