<?php

namespace common\models;

use common\components\AppStatus;
use Yii;

/**
 * This is the model class for table "action_flow".
 *
 * @property int $previous_action_id Foreign key to “action”  table for the previous action
 * @property int $next_action_id Foreign key to “action”  table for the next action
 * @property int $status Status of the previous action to allow the next one. 2=success, 1=partial, 4=failure, 3=not failed, 5=not succeeded, 7=any status
 *
 * @property Action $nextAction
 * @property Action $previousAction
 */
class ActionFlow extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'action_flow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['status'], 'default', 'value' => AppStatus::SUCCESS->value],
            [['status'], 'in', 'range' => AppStatus::getValuesForAction()],
            [['previous_action_id', 'next_action_id'], 'required'],
            [['previous_action_id', 'next_action_id', 'status'], 'integer'],
            [['previous_action_id', 'next_action_id'], 'unique', 'targetAttribute' => ['previous_action_id', 'next_action_id']],
            [['next_action_id'], 'exist', 'skipOnError' => true, 'targetClass' => Action::class, 'targetAttribute' => ['next_action_id' => 'id']],
            [['previous_action_id'], 'exist', 'skipOnError' => true, 'targetClass' => Action::class, 'targetAttribute' => ['previous_action_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'previous_action_id' => 'Foreign key to “action”  table for the previous action',
            'next_action_id' => 'Foreign key to “action”  table for the next action',
            'status' => 'Status of the previous action to allow the next one. 2=success, 1=partial, 4=failure, 3=not failed, 5=not succeeded, 7=any status',
        ];
    }

    /**
     * Gets query for [[NextAction]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getNextAction() {
        return $this->hasOne(Action::class, ['id' => 'next_action_id']);
    }

    /**
     * Gets query for [[PreviousAction]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getPreviousAction() {
        return $this->hasOne(Action::class, ['id' => 'previous_action_id']);
    }
}
