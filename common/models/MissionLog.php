<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission_log".
 *
 * @property int $mission_id Primary key
 * @property int $quest_id Primary key
 * @property string $name Name
 * @property string|null $image Image
 * @property string $description Short description
 *
 * @property QuestLog $questLog
 * @property Quest[] $quests
 */
class MissionLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mission_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image'], 'default', 'value' => null],
            [['mission_id', 'quest_id', 'name', 'description'], 'required'],
            [['mission_id', 'quest_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['mission_id', 'quest_id'], 'unique', 'targetAttribute' => ['mission_id', 'quest_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mission_id' => 'Primary key',
            'quest_id' => 'Primary key',
            'name' => 'Name',
            'image' => 'Image',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[QuestLog]].
     *
     * @return \yii\db\ActiveQuery<QuestLog>
     */
    public function getQuestLog()
    {
        return $this->hasOne(QuestLog::class, ['mission_id' => 'mission_id', 'quest_id' => 'quest_id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuests()
    {
        return $this->hasMany(Quest::class, ['id' => 'quest_id'])->viaTable('quest_log', ['mission_id' => 'mission_id', 'quest_id' => 'quest_id']);
    }
}
