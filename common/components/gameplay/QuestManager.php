<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\NarrativeComponent;
use common\helpers\SaveHelper;
use common\models\Chapter;
use common\models\events\EventFactory;
use common\models\Mission;
use common\models\Player;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Exception;
use RuntimeException;
use Yii;
use yii\helpers\ArrayHelper;

class QuestManager extends BaseManager
{

    // Context data
    public ?Quest $quest = null;
    public ?QuestProgress $questProgress = null;
    // Internal use
    private ?Player $player = null;
    private ?int $nextSequence = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->questProgress) {
            $this->quest = $this->questProgress->quest;
        }
        $this->player ??= $this->quest?->currentPlayer;
    }

    // --- Strict Accessors (The Level 8/9 "Secret Sauce") ---

    /**
     *
     * @return Quest
     * @throws RuntimeException
     */
    private function getQuest(): Quest
    {
        if ($this->quest === null) {
            throw new RuntimeException('QuestManager context error: Quest is missing.');
        }
        return $this->quest;
    }

    /**
     *
     * @return QuestProgress
     * @throws RuntimeException
     */
    private function getQuestProgress(): QuestProgress
    {
        if ($this->questProgress === null) {
            throw new RuntimeException('QuestManager context error: QuestProgress is missing.');
        }
        return $this->questProgress;
    }

    /**
     *
     * @return Player
     * @throws RuntimeException
     */
    private function getPlayer(): Player
    {
        if ($this->player === null) {
            throw new RuntimeException('QuestManager context error: Player is missing.');
        }
        return $this->player;
    }

    // --- Logic Methods ---

    /**
     *
     * @param int|null $currentTurn
     * @return QuestPlayer|null
     */
    private function getActiveQuestPlayer(?int $currentTurn = null): ?QuestPlayer
    {
        $quest = $this->getQuest();
        $query = QuestPlayer::find()
                ->where(['quest_id' => $quest->id])
                ->andWhere(['<>', 'status', AppStatus::LEFT->value]);

        if ($currentTurn) {
            $query->andWhere(['>', 'player_turn', $currentTurn]);
        }

        return $query->orderBy(['player_turn' => SORT_ASC])->one();
    }

    /**
     *
     * @return QuestPlayer|null
     */
    private function getNextQuestPlayer(): ?QuestPlayer
    {
        $quest = $this->getQuest();

        if ($quest->current_player_id === null) {
            // If there's no current player, we start from the beginning of the turn queue
            return $this->getActiveQuestPlayer();
        }

        $currentQuestPlayer = QuestPlayer::findOne([
            'quest_id' => $quest->id,
            'player_id' => $quest->current_player_id
        ]);
        $currentTurn = $currentQuestPlayer?->player_turn;

        // Find the next active player
        $nextQuestPlayer = $this->getActiveQuestPlayer($currentTurn);

        if ($nextQuestPlayer) {
            return $nextQuestPlayer;
        }

        // Next chronological active player is not found
        // retry without the player_turn criterion to start from the beginning
        if ($currentTurn) {
            return $this->getActiveQuestPlayer();
        }
        return null;
    }

    /**
     *
     * @param int $playerId
     * @return void
     */
    private function setQuestCurrentPlayerId(int $playerId): void
    {
        $quest = $this->getQuest();
        $progress = $this->getQuestProgress();

        $quest->current_player_id = $playerId;
        $this->save($quest);
        unset($quest->currentPlayer);
        $quest->refresh();

        $progress->current_player_id = $playerId;
        $this->save($progress);
        unset($progress->currentPlayer);
        $progress->refresh();
    }

    /**
     *
     * @return int
     */
    private function getLastTurnSequence(): int
    {
        $progress = $this->getQuestProgress();
        $nextSequence = QuestTurn::find()->where(['quest_progress_id' => $progress->id])->max('sequence');

        return is_scalar($nextSequence) ? (int) $nextSequence : 0;
    }

    /**
     *
     * @return QuestTurn|null
     * @throws Exception
     */
    private function setNextQuestTurn(): ?QuestTurn
    {
        $nextQuestPlayer = $this->getNextQuestPlayer();

        // The quest has no more active players, no new turns can be added.
        if (!$nextQuestPlayer) {
            return null;
        }

        $nextPlayerId = $nextQuestPlayer->player_id;
        $this->setQuestCurrentPlayerId($nextPlayerId);

        // Refresh context
        $quest = $this->getQuest();
        $this->player = Player::findOne($nextPlayerId);

        if ($this->player === null) {
            throw new RuntimeException("QuestManager context error: Player #{$nextPlayerId} not found.");
        }

        $this->nextSequence = $this->getLastTurnSequence() + 1;

        /** @var QuestTurn $questTurn */
        $questTurn = new QuestTurn([
            'player_id' => $nextPlayerId,
            'quest_progress_id' => $this->getQuestProgress()->id,
            'sequence' => $this->nextSequence,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        SaveHelper::save($questTurn);
        return $questTurn;
    }

    /**
     *
     * @param int|null $questProgressId
     * @param AppStatus $status
     * @return int
     */
    private function endCurrentTurn(?int $questProgressId = null, AppStatus $status = AppStatus::TERMINATED): int
    {
        return QuestTurn::updateAll(['status' => $status->value], [
                    'status' => AppStatus::IN_PROGRESS->value,
                    'quest_progress_id' => $questProgressId ?? $this->getQuestProgress()->id,
        ]);
    }

    /**
     *
     * @param int $questId
     * @param string $reason
     * @return int
     */
    private function endQuestPlayers(int $questId, string $reason): int
    {
        return QuestPlayer::updateAll([
                    'status' => AppStatus::LEFT->value,
                    'left_at' => time(),
                    'reason' => $reason,
                        ], [
                    'status' => [AppStatus::ONLINE->value, AppStatus::OFFLINE->value],
                    'quest_id' => $questId,
        ]);
    }

    /**
     *
     * @param int $questId
     * @return int
     */
    private function detachPlayersFromQuest(int $questId): int
    {
        return Player::updateAll(['quest_id' => null], ['quest_id' => $questId]);
    }

    /**
     *
     * @param AppStatus $status
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    private function gameOver(AppStatus $status): array
    {
        $quest = $this->getQuest();
        $progress = $this->getQuestProgress();
        $player = $this->getPlayer();

        $quest->status = $status->value;
        $quest->completed_at = time();
        $this->save($quest);

        $message = "The quest {$quest->name} is over with status {$status->getLabel()}!!!";

        $this->endCurrentQuestProgress($progress, $status);
        $this->endQuestPlayers($quest->id, $message);
        $this->detachPlayersFromQuest($quest->id);

        $detail = [
            'status' => $status->getLabel(),
            'playerName' => $player->name,
            'questName' => $quest->name,
            'timestamp' => time(),
        ];

        $event = $this->createQuestEvent('game-over', $message, $player, $detail);
        return [
            'error' => false,
            'msg' => $message,
            'event' => 'game-over',
            'payload' => $event->toArray(),
        ];
    }

    /**
     * Initialize the first QuestProgress when creating a new Quest
     *
     * @return bool
     * @throws Exception
     */
    public function addFirstQuestProgress(): bool
    {
        $quest = $this->getQuest();
        $chapter = $quest->currentChapter;
        if ($chapter === null) {
            throw new Exception("Current chapter not found for Quest #{$quest->id}");
        }

        $questProgress = $this->addQuestProgress((int) $chapter->first_mission_id);
        return $questProgress !== null;
    }

    /**
     *
     * @param QuestProgress $questProgress
     * @param AppStatus $status
     * @return void
     */
    private function endCurrentQuestProgress(
            QuestProgress $questProgress,
            AppStatus $status = AppStatus::TERMINATED,
    ): void
    {
        $this->endCurrentTurn($questProgress->id, $status);

        $questProgress->status = $status->value;
        $questProgress->completed_at = time();
        $this->save($questProgress);
    }

    /**
     * Add a QuestProgress model as an instance of a Mission model for a specific Quest
     *
     * @param int $missionId
     * @return QuestProgress|null
     * @throws Exception
     */
    private function addQuestProgress(int $missionId): ?QuestProgress
    {
        $mission = Mission::findOne($missionId);
        if (!$mission) {
            throw new Exception("Mission #{$missionId} not found");
        }

        $nextQuestPlayer = $this->getNextQuestPlayer();
        if (!$nextQuestPlayer) {
            return null;
        }

        $questProgress = $this->newQuestProgress($mission, $nextQuestPlayer->player_id);

        $actionManager = new ActionManager(['questProgress' => $questProgress]);
        $actionManager->addQuestActions($missionId);

        $this->setNextQuestTurn();

        return $questProgress;
    }

    /**
     *
     * @param Mission $mission
     * @param int $nextPlayerId
     * @return QuestProgress
     */
    private function newQuestProgress(Mission $mission, int $nextPlayerId): QuestProgress
    {
        $narrative = new NarrativeComponent(['mission' => $mission]);

        $questProgress = new QuestProgress([
            'quest_id' => $this->getQuest()->id,
            'mission_id' => $mission->id,
            'current_player_id' => $nextPlayerId,
            'description' => $narrative->renderDescription(),
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        $this->save($questProgress);
        $this->questProgress = $questProgress;

        return $questProgress;
    }

    /**
     *
     * @return int|null
     */
    private function getFirstMissionIdInNextChapter(): ?int
    {
        $currentProgress = $this->getQuestProgress();
        $mission = $currentProgress->mission;

        $nextChapters = Chapter::find()
                ->where(['story_id' => $mission->chapter->story_id])
                ->andWhere(['>', 'chapter_number', $mission->chapter->chapter_number])
                ->orderBy(['chapter_number' => SORT_ASC])
                ->all();

        foreach ($nextChapters as $chapter) {
            $firstMission = $chapter->firstMission;
            if ($firstMission) {
                return $firstMission->id;
            }
        }

        return null;
    }

    /**
     *
     * @return int|null
     */
    private function getNextDefaultMissionId(): ?int
    {
        $progress = $this->getQuestProgress();
        $nextMissionInChapter = Mission::find()
                ->where(['chapter_id' => $progress->mission->chapter_id])
                ->andWhere(['>', 'id', $progress->mission_id])
                ->orderBy(['id' => SORT_ASC])
                ->one();

        if ($nextMissionInChapter) {
            return $nextMissionInChapter->id;
        }
        return $this->getFirstMissionIdInNextChapter();
    }

    /**
     *
     * @param QuestProgress $currentQuestProgress
     * @param QuestProgress $nextQuestProgress
     * @return array{
     *       currentMissionId: int,
     *       currentMissionName: string,
     *       currentPlayerId: int,
     *       currentPlayerName: string|null,
     *       nextMissionId: int,
     *       nextMissionName: string,
     *       nextPlayerId: int,
     *       nextPlayerName: string|null,
     *       nextQuestProgressId: int,
     *       timestamp: int
     * }
     */
    private function getNextMissionDetail(QuestProgress $currentQuestProgress, QuestProgress $nextQuestProgress): array
    {
        $currentMission = $currentQuestProgress->mission;
        $nextMission = $nextQuestProgress->mission;
        $currentPlayer = $currentQuestProgress->currentPlayer;
        $nextPlayer = $nextQuestProgress->currentPlayer;

        return [
            'currentMissionId' => $currentMission->id,
            'currentMissionName' => $currentMission->name,
            'currentPlayerId' => $currentPlayer->id,
            'currentPlayerName' => $currentPlayer->name,
            'nextMissionId' => $nextMission->id,
            'nextMissionName' => $nextMission->name,
            'nextPlayerId' => $nextPlayer->id,
            'nextPlayerName' => $nextPlayer->name,
            'nextQuestProgressId' => $nextQuestProgress->id,
            'timestamp' => time(),
        ];
    }

    /**
     *
     * @param Quest $quest
     * @param int $nextChapterId
     * @return void
     * @throws Exception
     */
    private function synchronizeChapterId(Quest $quest, int $nextChapterId): void
    {
        if ($quest->current_chapter_id !== $nextChapterId) {
            $quest->current_chapter_id = $nextChapterId;

            // Persist quest without running validation, to avoid leaving current_chapter_id out of sync
            if (!$quest->save()) {
                throw new Exception('Could not update quest current chapter.');
            }
        }
    }

    /**
     *
     * @param Quest $quest
     * @param int $nextMissionId
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     * @throws Exception
     */
    private function setNextMission(Quest $quest, int $nextMissionId): array
    {
        $currentQuestProgress = $this->getQuestProgress();
        $currentPlayer = $quest->currentPlayer;

        if ($currentPlayer === null) {
            throw new Exception('No current player found for quest.');
        }

        $this->endCurrentQuestProgress($currentQuestProgress);
        $nextQuestProgress = $this->addQuestProgress($nextMissionId);

        if (!$nextQuestProgress) {
            $currentPlayerId = $quest->current_player_id ?? 'null';
            throw new Exception("Could not initialize next quest progress for Quest #{$quest->id}, Player #{$currentPlayerId}, Mission #{$nextMissionId}");
        }

        // Check if the new mission is empty
        if (empty($nextQuestProgress->remainingActions)) {
            Yii::debug("*** debug *** setNextMission - Mission #{$nextMissionId} is empty, skipping.");
            // End this empty mission progress
            $this->endCurrentQuestProgress($nextQuestProgress, AppStatus::COMPLETED);
            // Try to move to the next mission
            return $this->moveToNextMission();
        }

        // Update quest current chapter if needed
        $nextChapterId = $nextQuestProgress->mission->chapter_id;
        $this->synchronizeChapterId($quest, $nextChapterId);

        $detail = $this->getNextMissionDetail($currentQuestProgress, $nextQuestProgress);
        $message = "The mission '{$detail['currentMissionName']}' is over, let's move to '{$detail['nextMissionName']}'!!!";

        $event = $this->createQuestEvent('next-mission', $message, $currentPlayer, $detail);
        return [
            'error' => false,
            'msg' => $message,
            'event' => 'next-mission',
            'payload' => $event->toArray(),
        ];
    }

    /**
     *
     * @param int|null $nextMissionId
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    public function moveToNextMission(?int $nextMissionId = null): array
    {
        Yii::debug('*** debug *** moveToNextMission nextMissionId=' . ($nextMissionId ? $nextMissionId : 'null'));

        // If nextMissionId is the current one, we ignore it and look for the next default one.
        // This avoids infinite loops or trying to restart the current mission when it's finished.
        if ($nextMissionId && $nextMissionId === $this->getQuestProgress()->mission_id) {
            Yii::debug("QuestManager::moveToNextMission - nextMissionId is current mission, ignoring.");
            $nextMissionId = null;
        }

        if ($nextMissionId) {
            return $this->setNextMission($this->getQuest(), $nextMissionId);
        }

        $nextDefaultMissionId = $this->getNextDefaultMissionId();
        if ($nextDefaultMissionId) {
            return $this->moveToNextMission($nextDefaultMissionId);
        }

        return $this->gameOver(AppStatus::COMPLETED);
    }

    /**
     *
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    public function nextPlayer(): array
    {
        $this->endCurrentTurn();
        $oldPlayer = $this->getPlayer();
        $questTurn = $this->setNextQuestTurn();

        if (!$questTurn) {
            return $this->gameOver(AppStatus::ABORTED);
        }

        $newPlayer = $this->getPlayer();
        $message = 'Move to next player';
        $detail = [
            'currentPlayerId' => $oldPlayer->id,
            'currentPlayerName' => $oldPlayer->name,
            'questProgressId' => $this->getQuestProgress()->id,
            'nextPlayerId' => $newPlayer->id,
            'nextPlayerName' => $newPlayer->name,
            'nextTurnSequence' => $this->nextSequence,
            'timestamp' => time(),
        ];

        $event = $this->createQuestEvent('next-turn', $message, $oldPlayer, $detail);
        return [
            'error' => false,
            'msg' => $message,
            'event' => 'next-turn',
            'payload' => $event->toArray(),
        ];
    }

    /**
     *
     * @param string $eventType
     * @param string $eventDescription
     * @param Player|null $initiator
     * @param array<string, mixed> $detail
     * @return \common\models\events\Event
     * @throws Exception
     */
    private function createQuestEvent(
            string $eventType,
            string $eventDescription,
            ?Player $initiator,
            array $detail = [],
    ): \common\models\events\Event
    {
        Yii::debug("*** debug *** createQuestEvent - initiator={$initiator?->name}");

        try {
            $sessionId = Yii::$app->session->get('sessionId');
            $player = $initiator ?? $this->getPlayer();
            $quest = $this->getQuest();

            $data = [
                'action' => $eventDescription,
                'detail' => $detail,
            ];

            $event = EventFactory::createEvent($eventType, (string) $sessionId, $player, $quest, $data);
            $event->process();
            return $event;
        } catch (Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            throw new Exception('Error: ' . $e->getMessage());
        }
    }
}
