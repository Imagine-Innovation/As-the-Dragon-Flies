<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "quest_progress".
 *
 * @property int $id
 * @property int $quest_id Foreign key to "quest" table
 * @property int|null $current_room_id Foreign key to "room" table, current room of focus for the quest
 * @property int $step Narrative step or progression counter for the quest
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Quest $quest
 * @property Room $currentRoom
 */
class QuestProgress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%quest_progress}}'; // Using Yii's table prefix convention
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class, // Automatically handles created_at and updated_at
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quest_id'], 'required'],
            [['quest_id', 'current_room_id', 'step', 'created_at', 'updated_at'], 'integer'],
            [['step'], 'default', 'value' => 0],
            [['quest_id'], 'unique'], // Typically, one progress record per quest
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            [['current_room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::class, 'targetAttribute' => ['current_room_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quest_id' => 'Quest ID',
            'current_room_id' => 'Current Room ID',
            'step' => 'Narrative Step',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest()
    {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[CurrentRoom]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentRoom()
    {
        return $this->hasOne(Room::class, ['id' => 'current_room_id']);
    }

    /**
     * Finds or creates a QuestProgress record for a given quest.
     * If created, current_room_id can be optionally set.
     *
     * @param int $questId
     * @param int|null $initialRoomId
     * @return QuestProgress|null
     */
    public static function findOrCreate(int $questId, ?int $initialRoomId = null): ?QuestProgress
    {
        $progress = static::findOne(['quest_id' => $questId]);
        if ($progress === null) {
            $progress = new static();
            $progress->quest_id = $questId;
            if ($initialRoomId !== null) {
                $progress->current_room_id = $initialRoomId;
            }
            if (!$progress->save()) {
                Yii::error("Failed to create QuestProgress for quest_id {$questId}: " . print_r($progress->errors, true), __METHOD__);
                return null;
            }
        }
        return $progress;
    }
}
