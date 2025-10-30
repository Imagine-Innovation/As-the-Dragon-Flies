<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_action".
 *
 * @property int $quest_progress_id Foreign key to “quest_progress” table
 * @property int $action_id Foreign key to “action” table
 * @property int|null $status Status of the action
 * @property int $eligible Can be used in the following turns
 *
 * @property Action $action
 * @property QuestProgress $questProgress
 */
class QuestAction extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_action';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['status'], 'default', 'value' => null],
            [['eligible'], 'default', 'value' => 1],
            [['quest_progress_id', 'action_id', 'eligible'], 'required'],
            [['quest_progress_id', 'action_id', 'status', 'eligible'], 'integer'],
            [['quest_progress_id', 'action_id'], 'unique', 'targetAttribute' => ['quest_progress_id', 'action_id']],
            [['quest_progress_id'], 'exist', 'skipOnError' => true, 'targetClass' => QuestProgress::class, 'targetAttribute' => ['quest_progress_id' => 'id']],
            [['action_id'], 'exist', 'skipOnError' => true, 'targetClass' => Action::class, 'targetAttribute' => ['action_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'quest_progress_id' => 'Foreign key to “quest_progress” table',
            'action_id' => 'Foreign key to “action” table',
            'status' => 'Status of the action',
            'eligible' => 'Can be used in the following turns',
        ];
    }

    /**
     * Gets query for [[Action]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAction() {
        return $this->hasOne(Action::class, ['id' => 'action_id']);
    }

    /**
     * Gets query for [[QuestProgress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestProgress() {
        return $this->hasOne(QuestProgress::class, ['id' => 'quest_progress_id']);
    }
}
