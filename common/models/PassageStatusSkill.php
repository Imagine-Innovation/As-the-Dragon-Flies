<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage_status_skill".
 *
 * @property int $status_id Foreign key to "passage_status" table
 * @property int $skill_id Foreign key to "skill" table
 * @property int $success Percentage of exchange rate to succeed in opening the passageway
 *
 * @property Skill $skill
 * @property PassageStatus $status
 */
class PassageStatusSkill extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage_status_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['status_id', 'skill_id'], 'required'],
            [['status_id', 'skill_id', 'success'], 'integer'],
            [['status_id', 'skill_id'], 'unique', 'targetAttribute' => ['status_id', 'skill_id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => PassageStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'status_id' => 'Foreign key to \"passage_status\" table',
            'skill_id' => 'Foreign key to \"skill\" table',
            'success' => 'Percentage of exchange rate to succeed in opening the passageway',
        ];
    }

    /**
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkill() {
        return $this->hasOne(Skill::class, ['id' => 'skill_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus() {
        return $this->hasOne(PassageStatus::class, ['id' => 'status_id']);
    }
}
