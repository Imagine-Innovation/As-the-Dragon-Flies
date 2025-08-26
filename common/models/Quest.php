<?php

namespace common\models;

use common\components\AppStatus;
use Yii;

/**
 * This is the model class for table "quest".
 *
 * @property int $id Primary key
 * @property int $story_id Foreign key to "story" table
 * @property int|null $initiator_id Foreign key to "player" table
 * @property int $status Quest status (waiting, playing, paused, completed, aborted)
 * @property int $created_at Created at
 * @property int|null $started_at Started at
 * @property int|null $completed_at Completed at
 * @property int $local_time Local time
 * @property int $elapsed_time Elapsed time (minute)
 *
 * @property Player $initiator
 * @property Notification[] $notifications
 * @property Player[] $currentPlayers
 * @property Player[] $allPlayers
 * @property QuestLog[] $questLogs
 * @property QuestPlayer[] $questPlayers
 * @property QuestSession[] $questSessions
 * @property Story $story
 * @property UserLog[] $userLogs
 */
class Quest extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'quest';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['initiator_id', 'started_at', 'completed_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => AppStatus::WAITING->value],
            [['elapsed_time'], 'default', 'value' => 0],
            [['story_id'], 'required'],
            [['story_id', 'initiator_id', 'status', 'created_at', 'started_at', 'completed_at', 'local_time', 'elapsed_time'], 'integer'],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['initiator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['initiator_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'story_id' => 'Foreign key to \"story\" table',
            'initiator_id' => 'Foreign key to \"player\" table',
            'status' => 'Quest status (waiting, playing, paused, completed, aborted)',
            'created_at' => 'Created at',
            'started_at' => 'Started at',
            'completed_at' => 'Completed at',
            'local_time' => 'Local time',
            'elapsed_time' => 'Elapsed time (minute)',
        ];
    }

    /**
     * Gets query for [[Initiator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInitiator() {
        return $this->hasOne(Player::class, ['id' => 'initiator_id']);
    }

    /**
     * Gets query for [[Notifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications() {
        return $this->hasMany(Notification::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[CurrentPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentPlayers() {
        return $this->hasMany(Player::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[AllPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAllPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('questPlayers');
    }

    /**
     * Gets query for [[QuestLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestLogs() {
        return $this->hasMany(QuestLog::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[QuestPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestPlayers() {
        return $this->hasMany(QuestPlayer::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[QuestSessions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestSessions() {
        return $this->hasMany(QuestSession::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStory() {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }

    /**
     * Gets query for [[UserLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogs() {
        return $this->hasMany(UserLog::class, ['quest_id' => 'id']);
    }
}
