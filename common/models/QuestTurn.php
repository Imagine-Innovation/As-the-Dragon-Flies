<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest_turn".
 *
 * @property int $id Primary key
 * @property int $player_id Foreign key to “player” table
 * @property int $quest_progress_id Foreign key to “quest_progress” table
 * @property int $sequence Turn sequence
 * @property int $status Status
 * @property int $started_at Started at
 * @property int|null $ended_at Ended at
 * @property string|null $description Short description of what happened during the turn
 *
 * @property Player $player
 * @property QuestProgress $questProgress
 */
class QuestTurn extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_turn';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['ended_at', 'description'], 'default', 'value' => null],
            [['player_id', 'quest_progress_id', 'sequence', 'status', 'started_at'], 'required'],
            [['player_id', 'quest_progress_id', 'sequence', 'status', 'started_at', 'ended_at'], 'integer'],
            [['description'], 'string'],
            [['quest_progress_id'], 'exist', 'skipOnError' => true, 'targetClass' => QuestProgress::class, 'targetAttribute' => ['quest_progress_id' => 'id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'player_id' => 'Foreign key to “player” table',
            'quest_progress_id' => 'Foreign key to “quest_progress” table',
            'sequence' => 'Turn sequence',
            'status' => 'Status',
            'started_at' => 'Started at',
            'ended_at' => 'Ended at',
            'description' => 'Short description of what happened during the turn',
        ];
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }

    /**
     * Gets query for [[QuestProgress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestProgress() {
        return $this->hasOne(QuestProgress::class, ['id' => 'quest_progress_id']);
    }

}
