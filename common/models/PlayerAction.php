<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "player_action".
 *
 * @property int $id
 * @property int $game_round_id Foreign key to "game_round" table
 * @property int $player_id Foreign key to "player" table
 * @property string $action_type Type of action (e.g., "look_around", "skill_check", "move", "attack")
 * @property string|null $action_details JSON string containing parameters of the action (e.g., target, skill used)
 * @property int $is_successful Boolean flag (0 or 1) indicating if the action succeeded
 * @property string|null $outcome_description DM-generated narrative of what happened as a result of the action
 * @property int $created_at
 * @property int $updated_at
 *
 * @property GameRound $gameRound
 * @property Player $player
 */
class PlayerAction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%player_action}}';
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
        return [
            [['game_round_id', 'player_id', 'action_type', 'is_successful'], 'required'],
            [['game_round_id', 'player_id', 'created_at', 'updated_at'], 'integer'],
            [['is_successful'], 'boolean'],
            [['action_details', 'outcome_description'], 'string'],
            [['action_type'], 'string', 'max' => 50], // Adjust max length as needed
            [['game_round_id'], 'exist', 'skipOnError' => true, 'targetClass' => GameRound::class, 'targetAttribute' => ['game_round_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['action_details'], 'validateJsonOrNull'],
        ];
    }

    /**
     * Custom validator for JSON fields that can also be null.
     */
    public function validateJsonOrNull($attribute, $params)
    {
        if ($this->$attribute !== null && !empty($this->$attribute)) { // check if not empty too
            json_decode($this->$attribute);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($attribute, 'Must be a valid JSON string or null/empty.');
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
            'game_round_id' => 'Game Round ID',
            'player_id' => 'Player ID',
            'action_type' => 'Action Type',
            'action_details' => 'Action Details (JSON)',
            'is_successful' => 'Is Successful',
            'outcome_description' => 'Outcome Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[GameRound]].
     * @return ActiveQuery
     */
    public function getGameRound()
    {
        return $this->hasOne(GameRound::class, ['id' => 'game_round_id']);
    }

    /**
     * Gets query for [[Player]].
     * @return ActiveQuery
     */
    public function getPlayer()
    {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }
}
