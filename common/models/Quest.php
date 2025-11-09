<?php

namespace common\models;

use common\components\AppStatus;
use Yii;

/**
 * This is the model class for table "quest".
 *
 * @property int $id Primary key
 * @property int $story_id Foreign key to “story” table
 * @property int|null $current_chapter_id Optionnal foreign key to “chapter” table
 * @property int|null $current_player_id Optionnal foreign key to “player” table
 * @property int|null $initiator_id Optionnal foreign key to “player” table
 * @property string $name Quest name
 * @property string|null $description Description
 * @property string|null $image Image
 * @property int $status Quest status (waiting, playing, paused, completed, aborted)
 * @property int $created_at Created at
 * @property int|null $started_at Started at
 * @property int|null $completed_at Completed at
 * @property int $local_time Local time
 * @property int $elapsed_time Elapsed time (minute)
 *
 * @property Chapter $currentChapter
 * @property Player $currentPlayer
 * @property Player $initiator
 * @property Notification[] $notifications
 * @property Player[] $currentPlayers
 * @property Player[] $allPlayers
 * @property QuestPlayer[] $questPlayers
 * @property QuestProgress[] $questProgresses
 * @property QuestSession[] $questSessions
 * @property Story $story
 * @property UserLog[] $userLogs
 *
 * Custom properties
 *
 * @property QuestPlayer $currentQuestPlayer
 * @property QuestProgress $currentQuestProgress
 *
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
            [['current_chapter_id', 'current_player_id', 'initiator_id', 'description', 'image', 'started_at', 'completed_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => AppStatus::WAITING->value],
            [['elapsed_time'], 'default', 'value' => 0],
            [['story_id', 'name'], 'required'],
            [['story_id', 'current_chapter_id', 'current_player_id', 'initiator_id', 'status', 'created_at', 'started_at', 'completed_at', 'local_time', 'elapsed_time'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['current_chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['current_chapter_id' => 'id']],
            [['initiator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['initiator_id' => 'id']],
            [['current_player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['current_player_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'story_id' => 'Foreign key to “story” table',
            'current_chapter_id' => 'Optionnal foreign key to “chapter” table',
            'current_player_id' => 'Optionnal foreign key to “player” table',
            'initiator_id' => 'Optionnal foreign key to “player” table',
            'name' => 'Quest name',
            'description' => 'Description',
            'image' => 'Image',
            'status' => 'Quest status (waiting, playing, paused, completed, aborted)',
            'created_at' => 'Created at',
            'started_at' => 'Started at',
            'completed_at' => 'Completed at',
            'local_time' => 'Local time',
            'elapsed_time' => 'Elapsed time (minute)',
        ];
    }

    /**
     * Gets query for [[CurrentChapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentChapter() {
        return $this->hasOne(Chapter::class, ['id' => 'current_chapter_id']);
    }

    /**
     * Gets query for [[CurrentPlayer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentPlayer() {
        return $this->hasOne(Player::class, ['id' => 'current_player_id']);
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
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('quest_player', ['quest_id' => 'id']);
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
     * Gets query for [[QuestProgresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestProgresses() {
        return $this->hasMany(QuestProgress::class, ['quest_id' => 'id']);
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

    /**
     * Custom porperties
     */

    /**
     * Gets query for [[CurrentQuestPlayer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentQuestPlayer() {
        return $this->hasOne(QuestPlayer::class, ['quest_id' => 'id', 'player_id' => 'current_player_id']);
    }

    /**
     * Gets query for [[CurrentQuestProgress]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentQuestProgress() {
        //return $this->hasOne(QuestProgress::class, ['quest_id' => 'id', 'status' => AppStatus::IN_PROGRESS->value]);
        return $this->hasOne(QuestProgress::class, ['quest_id' => 'id'])
                        ->andWhere(['status' => AppStatus::IN_PROGRESS->value]);
    }
}
