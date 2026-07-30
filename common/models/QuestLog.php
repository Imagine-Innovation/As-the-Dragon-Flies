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
 * @property string $mission_name Mission name
 * @property string $action_name Action name
 * @property int $dc Difficulty Class (DC)
 * @property int $action_success Success
 * @property string|null $description Short description
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
            [['description'], 'default', 'value' => null],
            [['dc'], 'default', 'value' => 0],
            [['mission_name'], 'default', 'value' => 'Unknown'],
            [['action_name'], 'default', 'value' => 'Something'],
            [['action_success'], 'default', 'value' => 7],
            [['quest_id', 'player_id'], 'required'],
            [['quest_id', 'player_id', 'round', 'dc', 'action_success'], 'integer'],
            [['description'], 'string'],
            [['chapter_name', 'mission_name', 'action_name'], 'string', 'max' => 64],
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
            'mission_name' => 'Mission name',
            'action_name' => 'Action name',
            'dc' => 'Difficulty Class (DC)',
            'action_success' => 'Success',
            'description' => 'Short description',
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
