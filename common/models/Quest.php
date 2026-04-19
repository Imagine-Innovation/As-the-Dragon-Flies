<?php

namespace common\models;

use common\components\AppStatus;
use common\helpers\RichTextHelper;
use common\models\Mission;
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
 * @property Chapter|null $currentChapter
 * @property Player|null $currentPlayer
 * @property Player $initiator
 * @property Notification[] $notifications
 * @property Player[] $currentPlayers
 * @property Player[] $allPlayers
 * @property QuestPlayer[] $questPlayers
 * @property QuestProgress[] $questProgresses
 * @property QuestSession[] $questSessions
 * @property Story $story
 * @property AccessLog[] $accessLogs
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
    public static function tableName()
    {
        return 'quest';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'current_chapter_id',
                    'current_player_id',
                    'initiator_id',
                    'description',
                    'image',
                    'started_at',
                    'completed_at',
                ],
                'default',
                'value' => null,
            ],
            [['status'], 'default', 'value' => AppStatus::WAITING->value],
            [['elapsed_time'], 'default', 'value' => 0],
            [['story_id', 'name'], 'required'],
            [
                [
                    'story_id',
                    'current_chapter_id',
                    'current_player_id',
                    'initiator_id',
                    'status',
                    'created_at',
                    'started_at',
                    'completed_at',
                    'local_time',
                    'elapsed_time',
                ],
                'integer',
            ],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeMarkdownWithCache']],
            [['name', 'image'], 'string', 'max' => 64],
            [
                ['story_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Story::class,
                'targetAttribute' => ['story_id' => 'id'],
            ],
            [
                ['current_chapter_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Chapter::class,
                'targetAttribute' => ['current_chapter_id' => 'id'],
            ],
            [
                ['current_player_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Player::class,
                'targetAttribute' => ['current_player_id' => 'id'],
            ],
            [
                ['initiator_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Player::class,
                'targetAttribute' => ['initiator_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
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
     * @return \yii\db\ActiveQuery<Chapter>|null
     */
    public function getCurrentChapter()
    {
        return $this->hasOne(Chapter::class, ['id' => 'current_chapter_id']);
    }

    /**
     * Gets query for [[CurrentPlayer]].
     *
     * @return \yii\db\ActiveQuery<Player>|null
     */
    public function getCurrentPlayer()
    {
        return $this->hasOne(Player::class, ['id' => 'current_player_id']);
    }

    /**
     * Gets query for [[Initiator]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getInitiator()
    {
        return $this->hasOne(Player::class, ['id' => 'initiator_id']);
    }

    /**
     * Gets query for [[Notifications]].
     *
     * @return \yii\db\ActiveQuery<Notification>
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[CurrentPlayers]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getCurrentPlayers()
    {
        return $this->hasMany(Player::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[AllPlayers]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getAllPlayers()
    {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('quest_player', ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[QuestPlayers]].
     *
     * @return \yii\db\ActiveQuery<QuestPlayer>
     */
    public function getQuestPlayers()
    {
        return $this->hasMany(QuestPlayer::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[QuestProgresses]].
     *
     * @return \yii\db\ActiveQuery<QuestProgress>
     */
    public function getQuestProgresses()
    {
        return $this->hasMany(QuestProgress::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[QuestSessions]].
     *
     * @return \yii\db\ActiveQuery<QuestSession>
     */
    public function getQuestSessions()
    {
        return $this->hasMany(QuestSession::class, ['quest_id' => 'id']);
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery<Story>
     */
    public function getStory()
    {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }

    /**
     * Gets query for [[AccessLogs]].
     *
     * @return \yii\db\ActiveQuery<AccessLog>
     */
    public function getAccessLogs()
    {
        return $this->hasMany(AccessLog::class, ['quest_id' => 'id']);
    }

    /**
     * Custom porperties
     */

    /**
     * Gets query for [[CurrentQuestPlayer]].
     *
     * @return \yii\db\ActiveQuery<QuestPlayer>
     */
    public function getCurrentQuestPlayer()
    {
        return $this->hasOne(QuestPlayer::class, ['quest_id' => 'id', 'player_id' => 'current_player_id']);
    }

    /**
     * Gets query for [[CurrentQuestProgress]].
     *
     * @return \yii\db\ActiveQuery<QuestProgress>
     */
    public function getCurrentQuestProgress()
    {
        return $this->hasOne(QuestProgress::class, ['quest_id' => 'id'])->andWhere([
                    'status' => AppStatus::IN_PROGRESS->value,
        ]);
    }

    /**
     * Gets the total number of missions in the story associated with the quest.
     *
     * @return int
     */
    public function getTotalMissionsCount(): int
    {
        if ($this->isRelationPopulated('story') && $this->story->isRelationPopulated('chapters')) {
            $count = 0;
            foreach ($this->story->chapters as $chapter) {
                if ($chapter->isRelationPopulated('missions')) {
                    $count += count($chapter->missions);
                } else {
                    return (int) Mission::find()
                                    ->innerJoinWith('chapter')
                                    ->where(['chapter.story_id' => $this->story_id])
                                    ->count();
                }
            }
            return $count;
        }

        return (int) Mission::find()
                        ->innerJoinWith('chapter')
                        ->where(['chapter.story_id' => $this->story_id])
                        ->count();
    }

    /**
     * Gets the number of completed missions in the quest.
     * A mission is considered completed if its progress status is TERMINATED.
     *
     * @return int
     */
    public function getCompletedMissionsCount(): int
    {
        $completedStatuses = [AppStatus::COMPLETED->value, AppStatus::TERMINATED->value];
        if ($this->isRelationPopulated('questProgresses')) {
            $count = 0;
            foreach ($this->questProgresses as $progress) {
                if (in_array($progress->status, $completedStatuses)) {
                    $count++;
                }
            }
            return $count;
        }

        return (int) $this->getQuestProgresses()
                        ->andWhere(['status' => $completedStatuses])
                        ->count();
    }

    /**
     * Gets the progress percentage of the quest.
     *
     * @return int
     */
    public function getProgress(): int
    {
        $total = $this->getTotalMissionsCount();
        if ($total === 0) {
            return 0;
        }

        $progress = ($this->getCompletedMissionsCount() / $total) * 100;
        return (int) round($progress);
    }

    /**
     * @return Quest[]
     */
    public static function getActiveQuests(): array
    {
        return self::find()
                        ->where(['status' => [AppStatus::WAITING->value, AppStatus::PLAYING->value, AppStatus::PAUSED->value]])
                        ->with(['initiator', 'story.chapters.missions', 'questProgresses'])
                        ->orderBy(['started_at' => SORT_ASC])
                        ->all();
    }
}
