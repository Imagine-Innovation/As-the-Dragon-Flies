<?php

namespace common\models;

use common\components\AppStatus;
use Yii;

/**
 * This is the model class for table "action_interaction".
 *
 * @property int $previous_action_id Foreign key to “action”  table for the previous action
 * @property int $next_action_id Foreign key to “action”  table for the next action
 * @property int $status Status of the previous action to allow the next one. 500=success, 501=partial, 502=failure
 *
 * @property Action $nextAction
 * @property Action $previousAction
 */
class ActionInteraction extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'action_interaction';
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
            'status' => 'Status of the previous action to allow the next one. 500=success, 501=partial, 502=failure',
        ];
    }

    /**
     * Gets query for [[NextAction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNextAction() {
        return $this->hasOne(Action::class, ['id' => 'next_action_id']);
    }

    /**
     * Gets query for [[PreviousAction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreviousAction() {
        return $this->hasOne(Action::class, ['id' => 'previous_action_id']);
    }
}
