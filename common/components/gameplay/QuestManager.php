<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\helpers\FindModelHelper;
use common\helpers\LocalizeMessageHelper;
use common\helpers\SaveHelper;
use common\models\events\Event;
use common\models\events\EventFactory;
use common\models\Chapter;
use common\models\Mission;
use common\models\Player;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Exception;
use RuntimeException;
use Yii;

class QuestManager extends BaseManager
{

    // Context data
    public Quest $quest;
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
        $this->player ??= $this->quest->currentPlayer;
    }

    // --- Strict Accessors (The Level 8/9 "Secret Sauce") ---

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

    /**
     *
     * @return Player
     * @throws RuntimeException
     */
    private function getCurrentPlayer(): Player
    {
        $currentPlayer = $this->quest->currentPlayer;
        if ($currentPlayer === null) {
            throw new RuntimeException('QuestManager context error: Current player is missing.');
        }
        return $currentPlayer;
    }

    // --- Logic Methods ---

    /**
     *
     * @param int|null $currentTurn
     * @return QuestPlayer|null
     */
    private function getActiveQuestPlayer(?int $currentTurn = null): ?QuestPlayer
    {
        $query = QuestPlayer::find()
                ->where(['quest_id' => $this->quest->id])
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
        Yii::debug("*** debug *** QuestManager - getNextQuestPlayer()");
        if ($this->quest->current_player_id === null) {
            // If there's no current player, we start from the beginning of the turn queue
            return $this->getActiveQuestPlayer();
        }

        $currentQuestPlayer = QuestPlayer::findOne([
            'quest_id' => $this->quest->id,
            'player_id' => $this->quest->current_player_id
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
        Yii::debug("*** debug *** QuestManager - setQuestCurrentPlayerId(playerId={$playerId})");
        $progress = $this->getQuestProgress();

        $this->quest->current_player_id = $playerId;
        $this->save($this->quest);
        unset($this->quest->currentPlayer);
        $this->quest->refresh();

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
        $lastSequence = QuestTurn::find()
                ->where(['quest_progress_id' => $progress->id])
                ->max('sequence');

        return is_scalar($lastSequence) ? (int) $lastSequence : 0;
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
        return QuestTurn::updateAll(
                        [
                            'status' => $status->value,
                            'ended_at' => time(),
                        ],
                        [
                            'status' => AppStatus::IN_PROGRESS->value,
                            'quest_progress_id' => $questProgressId ?? $this->getQuestProgress()->id,
                        ]
                );
    }

    /**
     *
     * @param int $questId
     * @param string $reason
     * @return void
     */
    private function removePlayersFromQuest(int $questId, string $reason): void
    {
        Player::updateAll(['quest_id' => null], ['quest_id' => $questId]);

        QuestPlayer::updateAll(
                [
                    'status' => AppStatus::OVER->value,
                    'left_at' => time(),
                    'reason' => $reason,
                ],
                [
                    'status' => [AppStatus::ONLINE->value, AppStatus::OFFLINE->value],
                    'quest_id' => $questId,
                ]
        );
    }

    /**
     *
     * @param AppStatus $status
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    private function gameOver(AppStatus $status): array
    {
        $this->quest->status = $status->value;
        $this->quest->completed_at = time();
        $this->save($this->quest);

        $message = "The quest {$this->quest->name} is over with status {$status->getLabel()}!!!";

        $progress = $this->getQuestProgress();
        $player = $this->getPlayer();
        $playerName = $player->name ?? '';

        $this->endCurrentQuestProgress($progress, $status);
        $this->removePlayersFromQuest($this->quest->id, $message);

        $detail = [
            'status' => $status->getLabel(),
            'playerName' => $playerName,
            'questName' => $this->quest->name,
            'toastMessage' => LocalizeMessageHelper::gameOverToastMessage($playerName, $this->quest->name, $status->name),
            'timestamp' => time(),
        ];

        $event = $this->createQuestEvent('game-over', $message, $player, $detail);

        return [
            'error' => false, 'msg' => $message, 'event' => 'game-over',
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
        $chapter = $this->quest->currentChapter;
        if ($chapter === null) {
            throw new Exception("Current chapter not found for Quest #{$this->quest->id}");
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
    private function endCurrentQuestProgress(QuestProgress $questProgress, AppStatus $status = AppStatus::TERMINATED): void
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
        $nextQuestPlayer = $this->getNextQuestPlayer();
        if (!$nextQuestPlayer) {
            return null;
        }

        $this->questProgress = $this->setQuestProgress($this->quest->id, $missionId, $nextQuestPlayer->player_id);
        $this->save($this->questProgress);

        $actionManager = new ActionManager(['questProgress' => $this->questProgress]);
        $actionManager->addQuestActions($missionId);

        $this->setNextQuestTurn();

        return $this->questProgress;
    }

    /**
     *
     * @param int $questId
     * @param int $missionId
     * @param int $playerId
     * @return QuestProgress
     */
    private function setQuestProgress(int $questId, int $missionId, int $playerId): QuestProgress
    {
        $questProgress = QuestProgress::findOne(['quest_id' => $questId, 'mission_id' => $missionId]);

        if ($questProgress) {
            // Reactivate existing progress
            $questProgress->status = AppStatus::IN_PROGRESS->value;
            $questProgress->current_player_id = $playerId;
            $questProgress->completed_at = null;
        } else {
            $questProgress = $this->newQuestProgress($missionId, $playerId);
        }

        return $questProgress;
    }

    /**
     *
     * @param int $missionId
     * @param int $nextPlayerId
     * @return QuestProgress
     */
    private function newQuestProgress(int $missionId, int $nextPlayerId): QuestProgress
    {
        $mission = FindModelHelper::findMission($missionId);

        $questProgress = new QuestProgress([
            'quest_id' => $this->quest->id,
            'mission_id' => $missionId,
            'current_player_id' => $nextPlayerId,
            'description' => $mission->description,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

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
            'toastMessage' => LocalizeMessageHelper::nextMissionToastMessage($currentPlayer->name, $currentMission->name, $nextPlayer->name, $nextMission->name),
            'turnMessage' => LocalizeMessageHelper::nextTurnMessage($nextPlayer->name),
            'timestamp' => time(),
        ];
    }

    /**
     *
     * @param int $nextChapterId
     * @return void
     * @throws Exception
     */
    private function synchronizeQuestWithChapter(int $nextChapterId): void
    {
        if ($this->quest->current_chapter_id !== $nextChapterId) {
            $this->quest->current_chapter_id = $nextChapterId;

            // Persist quest without running validation,
            // to avoid leaving current_chapter_id out of sync
            if (!$this->quest->save()) {
                throw new Exception('Could not update quest current chapter.');
            }
        }
    }

    /**
     *
     * @param int $nextMissionId
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     * @throws Exception
     */
    private function setNextMission(int $nextMissionId): array
    {
        Yii::debug("*** debug *** QuestManager - setNextMission(nextMissionId={$nextMissionId})");
        $currentPlayer = $this->getCurrentPlayer();

        $currentQuestProgress = $this->getQuestProgress();
        $this->endCurrentQuestProgress($currentQuestProgress);
        $nextQuestProgress = $this->addQuestProgress($nextMissionId);

        if (!$nextQuestProgress) {
            Yii::debug("Could not initialize next quest progress for Quest #{$this->quest->id}, Player #{$this->quest->current_player_id}, Mission #{$nextMissionId}. Forcing game over.");
            return $this->gameOver(AppStatus::ABORTED);
        }

        // Check if the new mission is empty
        if (empty($nextQuestProgress->remainingActions)) {
            Yii::debug("setNextMission - Mission #{$nextMissionId} is empty, skipping.");
            $this->endCurrentQuestProgress($nextQuestProgress, AppStatus::TERMINATED);
            return $this->moveToNextDefaultMission();
        }

        // Update quest current chapter if needed
        $this->synchronizeQuestWithChapter($nextQuestProgress->mission->chapter_id);

        $detail = $this->getNextMissionDetail($currentQuestProgress, $nextQuestProgress);
        $message = "The mission '{$detail['currentMissionName']}' is over, let's move to '{$detail['nextMissionName']}'!!!";

        $event = $this->createQuestEvent('next-mission', $message, $currentPlayer, $detail);
        return ['error' => false, 'msg' => $message, 'event' => 'next-mission', 'payload' => $event->toArray()];
    }

    /**
     *
     * @param int|null $nextMissionId
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    public function moveToNextMission(?int $nextMissionId): array
    {
        Yii::debug("*** debug *** QuestManager::moveToNextMission nextMissionId={$nextMissionId}");

        $status = AppStatus::from($this->quest->status);
        if ($status === AppStatus::COMPLETED || $status === AppStatus::ABORTED) {
            Yii::debug("*** debug *** QuestManager::moveToNextMission - Quest #{$this->quest->id} is already over with status " . $status->getLabel());
            return [
                'error' => false,
                'msg' => "Quest #{$this->quest->id} is already over with status " . $status->getLabel(),
            ];
        }
        if ($nextMissionId) {
            return $this->setNextMission($nextMissionId);
        }

        Yii::debug("*** debug *** QuestManager::moveToNextMission - No more missions, game over.");
        return $this->gameOver(AppStatus::COMPLETED);
    }

    /**
     *
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    public function moveToNextDefaultMission(): array
    {
        Yii::debug('*** debug *** QuestManager::moveToNextDefaultMission');

        $nextDefaultMissionId = $this->getNextDefaultMissionId();
        Yii::debug("*** debug *** QuestManager::moveToNextDefaultMission - nextDefaultMissionId=" . ($nextDefaultMissionId ?? 'null'));
        if ($nextDefaultMissionId) {
            return $this->moveToNextMission($nextDefaultMissionId);
        }

        Yii::debug("*** debug *** QuestManager::moveToNextDefaultMission - No more missions, game over.");
        return $this->gameOver(AppStatus::COMPLETED);
    }

    /**
     *
     * @return array{error: bool, msg: string, event?: string, payload?: array<string, mixed>}
     */
    public function nextPlayer(): array
    {
        $this->endCurrentTurn();
        $currentPlayer = $this->getPlayer();
        $questTurn = $this->setNextQuestTurn();
        if (!$questTurn) {
            return $this->gameOver(AppStatus::ABORTED);
        }

        $nextPlayer = $this->getPlayer();
        $message = 'Move to next player';

        $detail = $this->setNextPlayerDetail($currentPlayer, $nextPlayer);

        $event = $this->createQuestEvent('next-turn', $message, $currentPlayer, $detail);
        return ['error' => false, 'msg' => $message, 'event' => 'next-turn', 'payload' => $event->toArray()];
    }

    /**
     *
     * @param Player $currentPlayer
     * @param Player $nextPlayer
     *
     * @return array{
     *     currentPlayerId: int,
     *     currentPlayerName: string,
     *     questProgressId: int,
     *     nextPlayerId: int,
     *     nextPlayerName: string,
     *     nextTurnSequence: int,
     *     toastMessage: array{current: string, other: string},
     *     turnMessage: array{current: string, other: string},
     *     timestamp: int
     * }
     */
    private function setNextPlayerDetail(Player $currentPlayer, Player $nextPlayer): array
    {
        $questProgress = $this->getQuestProgress();

        return [
            'currentPlayerId' => $currentPlayer->id,
            'currentPlayerName' => $currentPlayer->name ?? '',
            'questProgressId' => $questProgress->id,
            'nextPlayerId' => $nextPlayer->id,
            'nextPlayerName' => $nextPlayer->name ?? '',
            'nextTurnSequence' => $this->nextSequence ?? 1,
            'toastMessage' => LocalizeMessageHelper::nextPlayerToastMessage($currentPlayer->name, $nextPlayer->name),
            'turnMessage' => LocalizeMessageHelper::nextTurnMessage($nextPlayer->name),
            'timestamp' => time()
        ];
    }

    /**
     *
     * @param string $eventType
     * @param string $eventDescription
     * @param Player|null $initiator
     * @param array<string, mixed> $detail
     * @return Event
     * @throws Exception
     */
    private function createQuestEvent(string $eventType, string $eventDescription, ?Player $initiator, array $detail = []): Event
    {
        Yii::debug("createQuestEvent - initiator={$initiator?->name}");

        try {
            /** @var string $sessionId */
            $sessionId = Yii::$app->session->get('sessionId');
            $player = $initiator ?? $this->getPlayer();

            $data = [
                'action' => $eventDescription,
                'detail' => $detail,
            ];

            $event = EventFactory::createEvent($eventType, $sessionId, $player, $this->quest, $data);
            $event->process();
            return $event;
        } catch (Exception $e) {
            Yii::debug("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            throw new Exception('Error: ' . $e->getMessage());
        }
    }
}
