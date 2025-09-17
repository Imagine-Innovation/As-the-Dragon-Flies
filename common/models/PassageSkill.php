<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage_skill".
 *
 * @property int $skill_id Foreign key to "skill" table
 * @property int $passage_id Foreign key to "passage" table
 * @property int $success Percentage of exchange rate to succeed in opening the passageway
 *
 * @property Passage $passage
 * @property Skill $skill
 */
class PassageSkill extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['success'], 'default', 'value' => 100],
            [['skill_id', 'passage_id'], 'required'],
            [['skill_id', 'passage_id', 'success'], 'integer'],
            [['skill_id', 'passage_id'], 'unique', 'targetAttribute' => ['skill_id', 'passage_id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
            [['passage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Passage::class, 'targetAttribute' => ['passage_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'skill_id' => 'Foreign key to \"skill\" table',
            'passage_id' => 'Foreign key to \"passage\" table',
            'success' => 'Percentage of exchange rate to succeed in opening the passageway',
        ];
    }

    /**
     * Gets query for [[Passage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassage() {
        return $this->hasOne(Passage::class, ['id' => 'passage_id']);
    }

    /**
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkill() {
        return $this->hasOne(Skill::class, ['id' => 'skill_id']);
    }

}
