<?php

namespace common\models;

use common\components\AppStatus;
use Yii;

/**
 * This is the model class for table "quest_player".
 *
 * @property int $quest_id Foreign key to "quest" table
 * @property int $player_id Foreign key to "player" table
 * @property int $onboarded_at Onboarded at
 * @property int $status Player status in the quest
 * @property int|null $left_at The player left at
 * @property string|null $reason Reason why the player left
 *
 * @property Player $player
 * @property Quest $quest
 */
class QuestPlayer extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest_player';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['left_at', 'reason'], 'default', 'value' => null],
            [['onboarded_at'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => AppStatus::ONLINE->value],
            [['quest_id', 'player_id'], 'required'],
            [['quest_id', 'player_id', 'onboarded_at', 'status', 'left_at'], 'integer'],
            [['reason'], 'string', 'max' => 256],
            [['quest_id', 'player_id'], 'unique', 'targetAttribute' => ['quest_id', 'player_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'quest_id' => 'Foreign key to \"quest\" table',
            'player_id' => 'Foreign key to \"player\" table',
            'onboarded_at' => 'Onboarded at',
            'status' => 'Player status in the quest',
            'left_at' => 'The player left at',
            'reason' => 'Reason why the player left',
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
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }
}
