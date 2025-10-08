<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_log".
 *
 * @property int $id Primary key
 * @property int $quest_id Foreign key to “quest” table
 * @property int $status_from Initial quest status
 * @property int $status_to current status
 * @property int $changed_at Change timestamp
 *
 * @property Quest $quest
 */
class QuestLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['quest_id', 'status_from', 'status_to', 'changed_at'], 'required'],
            [['quest_id', 'status_from', 'status_to', 'changed_at'], 'integer'],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'quest_id' => 'Foreign key to “quest” table',
            'status_from' => 'Initial quest status',
            'status_to' => 'current status',
            'changed_at' => 'Change timestamp',
        ];
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

}
