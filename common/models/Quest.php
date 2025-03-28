<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quest".
 *
 * @property int $id Primary key
 * @property int $story_id Foreign key to "story" table
 * @property int $status Quest status (waiting, playing, paused, completed, aborted)
 * @property int $created_at Created at
 * @property int|null $started_at Started at
 * @property int $local_time Local time
 * @property int $elapsed_time Elapsed time (minute)
 * @property int|null $last_notification_at Store the last notification timestamp
 *
 * @property Notification[] $notifications
 * @property Player[] $players
 * @property Player[] $everyPlayers
 * @property QuestChat[] $questChats
 * @property QuestLog[] $questLogs
 * @property QuestPlayer[] $questPlayers
 * @property Story $story
 * @property UserLog[] $userLogs
 */
class Quest extends \yii\db\ActiveRecord {

    const STATUS_WAITING = 0;   // When creating a new quest, players are waiting in the tavern
    const STATUS_PLAYING = 1;   // Quest is actually started
    const STATUS_PAUSED = 2;    // Quest is paused. When resuming status switches to "Playing"
    const STATUS_COMPLETED = 3; // Players reached the end of the quest
    const STATUS_ABORTED = 9;   // Aborted on admin or owner request

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
            [['story_id'], 'required'],
            [['story_id', 'status', 'created_at', 'started_at', 'local_time', 'elapsed_time', 'last_notification_at'], 'integer'],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'story_id' => 'Foreign key to \"story\" table',
            'status' => 'Quest status (waiting, playing, paused, completed, aborted)',
            'created_at' => 'Created at',
            'started_at' => 'Started at',
            'local_time' => 'Local time',
            'elapsed_time' => 'Elapsed time (minute)',
            'last_notification_at' => 'Store the last notification timestamp',
        ];
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
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[EveryPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEveryPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('questPlayers');
    }

    /**
     * Gets query for [[QuestChats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestChats() {
        return $this->hasMany(QuestChat::class, ['quest_id' => 'id']);
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
