<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "game_round".
 *
 * @property int $id
 * @property int $quest_id Foreign key to "quest" table
 * @property int $quest_progress_id Foreign key to "quest_progress" table
 * @property int $round_number Current round number for this context
 * @property string|null $round_start_description Textual description for the start of this round
 * @property string $status Status of the round (e.g., 'active', 'completed', 'paused') - see AppStatus
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Quest $quest
 * @property QuestProgress $questProgress
 * @property Room $room via QuestProgress
 * @property PlayerAction[] $playerActions Player actions taken in this round
 */
use common\components\AppStatus; // Import AppStatus

class GameRound extends \yii\db\ActiveRecord
{
    // No longer need local consts for status, will use AppStatus directly.

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%game_round}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // Prepare status values from AppStatus for rule validation
        $validStatusValues = [
            AppStatus::PLAYING->value,    // Using AppStatus::PLAYING for an active round
            AppStatus::COMPLETED->value,  // Using AppStatus::COMPLETED for a completed round
            AppStatus::PAUSED->value,     // Using AppStatus::PAUSED for a paused round
        ];

        return [
            [['quest_id', 'quest_progress_id', 'round_number', 'status'], 'required'],
            [['quest_id', 'quest_progress_id', 'round_number', 'created_at', 'updated_at'], 'integer'], // Removed current_player_id
            [['round_start_description'], 'string'], // Removed player_turn_order, actions_taken_this_round
            [['status'], 'integer'],
            [['status'], 'in', 'range' => $validStatusValues],
            [['round_number'], 'default', 'value' => 1],
            [['status'], 'default', 'value' => AppStatus::PLAYING->value],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            [['quest_progress_id'], 'exist', 'skipOnError' => true, 'targetClass' => QuestProgress::class, 'targetAttribute' => ['quest_progress_id' => 'id']],
            // Removed FK check for current_player_id
            // Removed validateJsonOrNull for player_turn_order, actions_taken_this_round
        ];
    }

    /**
     * Custom validator for JSON fields that can also be null.
     */
    public function validateJsonOrNull($attribute, $params)
    {
        if ($this->$attribute !== null) {
            json_decode($this->$attribute);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($attribute, 'Must be a valid JSON string or null.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quest_id' => 'Quest ID',
            'quest_progress_id' => 'Quest Progress ID',
            'round_number' => 'Round Number',
            'round_start_description' => 'Round Start Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Quest]].
     * @return ActiveQuery
     */
    public function getQuest()
    {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[QuestProgress]].
     * @return ActiveQuery
     */
    public function getQuestProgress()
    {
        return $this->hasOne(QuestProgress::class, ['id' => 'quest_progress_id']);
    }

    /**
     * Gets the Room associated with this GameRound through QuestProgress.
     * @return ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::class, ['id' => 'current_room_id'])
            ->via('questProgress');
    }

    /**
     * Gets query for [[PlayerActions]].
     * Represents all actions taken by players within this specific game round.
     * @return ActiveQuery
     */
    public function getPlayerActions()
    {
        return $this->hasMany(PlayerAction::class, ['game_round_id' => 'id']);
    }

    /**
     * Finds the currently active GameRound for a given quest and quest_progress_id.
     * @param int $questId
     * @param int|null $questProgressId
     * @return GameRound|null
     */
    public static function findActive(int $questId, ?int $questProgressId = null): ?GameRound
    {
        $query = static::find()->where(['quest_id' => $questId, 'status' => AppStatus::PLAYING->value]);
        if ($questProgressId !== null) {
            $query->andWhere(['quest_progress_id' => $questProgressId]);
        }
        // If multiple are active for the same quest_progress_id (shouldn't happen with good logic),
        // get the one most recently started (highest round number, then latest created).
        return $query->orderBy(['round_number' => SORT_DESC, 'created_at' => SORT_DESC, 'id' => SORT_DESC])->one();
    }

    // The initializePlayersForRound method is no longer needed in its previous form
    // as turn order and current player are not managed this way.
    // If a list of participants for the round is needed on this model, it could be added.
    // For now, participants are derived from Quest->getCurrentPlayers().
}
