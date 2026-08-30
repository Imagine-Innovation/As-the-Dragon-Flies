<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_log".
 *
 * @property int $id Primary key
 * @property int $quest_id Foreign key to “quest” table
 * @property int $player_id Foreign key to “player” table
 * @property int $chapter_id Foreign key to “chapter_log” table
 * @property int $mission_id Foreign key to “mission_log” table
 * @property int $round Round
 * @property string $action_name Action name
 * @property string|null $action_description Action description
 * @property string|null $dice_roll Dice roll
 * @property string|null $result Result
 * @property string|null $description Result description
 *
 * @property ChapterLog $chapterLog
 * @property MissionLog $missionLog
 * @property Player $player
 * @property Quest $quest
 */
class QuestLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quest_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['action_description', 'dice_roll', 'result', 'description'], 'default', 'value' => null],
            [['round'], 'default', 'value' => 0],
            [['action_name'], 'default', 'value' => 'Something'],
            [['quest_id', 'player_id', 'chapter_id', 'mission_id'], 'required'],
            [['quest_id', 'player_id', 'chapter_id', 'mission_id', 'round'], 'integer'],
            [['action_description', 'result', 'description'], 'string'],
            [['action_name', 'dice_roll'], 'string', 'max' => 64],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['chapter_id', 'quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChapterLog::class, 'targetAttribute' => ['chapter_id' => 'chapter_id', 'quest_id' => 'quest_id']],
            [['mission_id', 'quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => MissionLog::class, 'targetAttribute' => ['mission_id' => 'mission_id', 'quest_id' => 'quest_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'quest_id' => 'Foreign key to “quest” table',
            'player_id' => 'Foreign key to “player” table',
            'chapter_id' => 'Foreign key to “chapter_log” table',
            'mission_id' => 'Foreign key to “mission_log” table',
            'round' => 'Round',
            'action_name' => 'Action name',
            'action_description' => 'Action description',
            'dice_roll' => 'Dice roll',
            'result' => 'Result',
            'description' => 'Result description',
        ];
    }

    /**
     * Gets query for [[ChapterLog]].
     *
     * @return \yii\db\ActiveQuery<ChapterLog>
     */
    public function getChapterLog()
    {
        return $this->hasOne(ChapterLog::class, ['chapter_id' => 'chapter_id', 'quest_id' => 'quest_id']);
    }

    /**
     * Gets query for [[MissionLog]].
     *
     * @return \yii\db\ActiveQuery<MissionLog>
     */
    public function getMissionLog()
    {
        return $this->hasOne(MissionLog::class, ['mission_id' => 'mission_id', 'quest_id' => 'quest_id']);
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayer()
    {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuest()
    {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }
}
