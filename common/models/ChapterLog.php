<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chapter_log".
 *
 * @property int $chapter_id Primary key
 * @property int $quest_id Primary key
 * @property string $name Name
 * @property string|null $image Image
 * @property string $description Short description
 *
 * @property QuestLog $questLog
 * @property Quest[] $quests
 */
class ChapterLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chapter_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image'], 'default', 'value' => null],
            [['chapter_id', 'quest_id', 'name', 'description'], 'required'],
            [['chapter_id', 'quest_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['chapter_id', 'quest_id'], 'unique', 'targetAttribute' => ['chapter_id', 'quest_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'chapter_id' => 'Primary key',
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
        return $this->hasOne(QuestLog::class, ['chapter_id' => 'chapter_id', 'quest_id' => 'quest_id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuests()
    {
        return $this->hasMany(Quest::class, ['id' => 'quest_id'])->viaTable('quest_log', ['chapter_id' => 'chapter_id', 'quest_id' => 'quest_id']);
    }
}
