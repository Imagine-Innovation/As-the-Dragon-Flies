<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_progress".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to “mission” table
 * @property int $quest_id Foreign key to “quest” table
 * @property int $current_player_id Foreign key to “player” table
 * @property string|null $description Short description
 * @property int $status Progress status
 * @property int|null $started_at Started at
 * @property int|null $completed_at Completed at
 *
 * @property Action[] $actions
 * @property Mission $mission
 * @property Quest $quest
 * @property QuestPlayer $questPlayer
 * @property QuestAction[] $questActions
 * @property QuestTurn[] $questTurns
 */
class QuestProgress extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_progress';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'started_at', 'completed_at'], 'default', 'value' => null],
            [['mission_id', 'quest_id', 'current_player_id', 'status'], 'required'],
            [['mission_id', 'quest_id', 'current_player_id', 'status', 'started_at', 'completed_at'], 'integer'],
            [['description'], 'string'],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['quest_id', 'current_player_id'], 'exist', 'skipOnError' => true, 'targetClass' => QuestPlayer::class, 'targetAttribute' => ['quest_id' => 'quest_id', 'current_player_id' => 'player_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to “mission” table',
            'quest_id' => 'Foreign key to “quest” table',
            'current_player_id' => 'Foreign key to “player” table',
            'description' => 'Short description',
            'status' => 'Progress status',
            'started_at' => 'Started at',
            'completed_at' => 'Completed at',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['id' => 'action_id'])->viaTable('quest_action', ['quest_progress_id' => 'id']);
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[QuestPlayer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestPlayer() {
        return $this->hasOne(QuestPlayer::class, ['quest_id' => 'quest_id', 'player_id' => 'current_player_id']);
    }

    /**
     * Gets query for [[QuestActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestActions() {
        return $this->hasMany(QuestAction::class, ['quest_progress_id' => 'id']);
    }

    /**
     * Gets query for [[QuestTurns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestTurns() {
        return $this->hasMany(QuestTurn::class, ['quest_progress_id' => 'id']);
    }
}
