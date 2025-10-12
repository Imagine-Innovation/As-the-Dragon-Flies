<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "action_type_skill".
 *
 * @property int $action_type_id
 * @property int $skill_id
 *
 * @property ActionType $actionType
 * @property Skill $skill
 */
class ActionTypeSkill extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'action_type_skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['action_type_id', 'skill_id'], 'required'],
            [['action_type_id', 'skill_id'], 'integer'],
            [['action_type_id', 'skill_id'], 'unique', 'targetAttribute' => ['action_type_id', 'skill_id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
            [['action_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ActionType::class, 'targetAttribute' => ['action_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'action_type_id' => 'Action Type ID',
            'skill_id' => 'Skill ID',
        ];
    }

    /**
     * Gets query for [[ActionType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActionType() {
        return $this->hasOne(ActionType::class, ['id' => 'action_type_id']);
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
