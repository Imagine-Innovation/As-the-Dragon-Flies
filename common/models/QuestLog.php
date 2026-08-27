<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_log".
 *
 * @property int $id Primary key
 * @property int $quest_id Foreign key to “quest” table
 * @property int $player_id Foreign key to “player” table
 * @property int $round Round
 * @property string $chapter_name Chapter name
 * @property string|null $chapter_description Chapter description
 * @property string $mission_name Mission name
 * @property string|null $mission_description Mission description
 * @property string $action_name Action name
 * @property string|null $action_description Action description
 * @property string|null $dice_roll Dice roll
 * @property string|null $result Result
 * @property string|null $description Result description
 *
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
            [['chapter_description', 'mission_description', 'action_description', 'dice_roll', 'result', 'description'], 'default', 'value' => null],
            [['round'], 'default', 'value' => 0],
            [['mission_name'], 'default', 'value' => 'Unknown'],
            [['action_name'], 'default', 'value' => 'Something'],
            [['quest_id', 'player_id'], 'required'],
            [['quest_id', 'player_id', 'round'], 'integer'],
            [['chapter_description', 'mission_description', 'action_description', 'result', 'description'], 'string'],
            [['chapter_name', 'mission_name', 'action_name', 'dice_roll'], 'string', 'max' => 64],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
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
            'round' => 'Round',
            'chapter_name' => 'Chapter name',
            'chapter_description' => 'Chapter description',
            'mission_name' => 'Mission name',
            'mission_description' => 'Mission description',
            'action_name' => 'Action name',
            'action_description' => 'Action description',
            'dice_roll' => 'Dice roll',
            'result' => 'Result',
            'description' => 'Result description',
        ];
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
