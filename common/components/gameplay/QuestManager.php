<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\NarrativeComponent;
use common\models\events\EventFactory;
use common\models\Chapter;
use common\models\Mission;
use common\models\Player;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Yii;
use yii\helpers\ArrayHelper;

class QuestManager extends BaseManager
{

    // Context data
    // Public facade
    public ?Quest $quest = null;
    public ?QuestProgress $questProgress = null;
    // internal use
    private ?Player $player = null;
    private ?int $nextSequence = null;

    /**
     *
     * @param array<string, mixed> $config
     */
    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        if ($this->questProgress) {
            $this->quest = $this->questProgress->quest;
        }
        $this->player ??= $this->quest?->currentPlayer;
    }

    /**
     *
     * @param int|null $currentTurn
     * @return QuestPlayer|null
     */
    private function getActiveQuestPlayer(?int $currentTurn = null): ?QuestPlayer {
        // Find the next active player
        $query = QuestPlayer::find()
                ->where(['quest_id' => $this->quest->id])
                ->andWhere(['<>', 'status', AppStatus::LEFT->value]);

        if ($currentTurn) {
            $query->andWhere(['>', 'player_turn', $currentTurn]);
        }

        return $query->orderBy(['player_turn' => SORT_ASC])
                        ->one();
    }

    /**
     *
     * @return QuestPlayer|null
     */
    private function getNextQuestPlayer(): ?QuestPlayer {
        $currentTurn = $this->quest?->currentQuestPlayer?->player_turn;

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
    private function setQuestCurrentPlayerId(int $playerId): void {
        $this->quest->current_player_id = $playerId;
        $this->save($this->quest);

        $this->questProgress->current_player_id = $playerId;
        $this->save($this->questProgress);
    }

    /**
     *
     * @return int
     */
    private function getLastTurnSequence(): int {

        $questProgressId = $this->questProgress->id;

        $nextSequence = QuestTurn::find()
                ->where(['quest_progress_id' => $questProgressId])
                ->max('sequence');

        return $nextSequence ?? 0;
    }

    /**
     *
     * @return QuestTurn|null
     * @throws \Exception
     */
    private function setNextQuestTurn(): ?QuestTurn {
        $nextQuestPlayer = $this->getNextQuestPlayer();

        // The quest has no more active players, no new turns can be added.
        if (!$nextQuestPlayer) {
            return null;
        }
        $nextPlayerId = $nextQuestPlayer->player_id;

        $this->setQuestCurrentPlayerId($nextPlayerId);
        // Update component context
        $this->player = $this->quest->currentPlayer;

        $this->nextSequence = $this->getLastTurnSequence() + 1;

        $questTurn = new QuestTurn([
            'player_id' => $nextPlayerId,
            'quest_progress_id' => $this->questProgress->id,
            'sequence' => $this->nextSequence,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time()
        ]);

        if (!$questTurn->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questTurn->errors, 0, false)));
        }
        return $questTurn;
    }

    /**
     *
     * @param int|null $questProgressId
     * @param AppStatus|null $status
     * @return int
     */
    private function endCurrentTurn(?int $questProgressId = null, ?AppStatus $status = AppStatus::TERMINATED): int {
        return QuestTurn::updateAll(
                        ['status' => $status->value],
                        [
                            'status' => AppStatus::IN_PROGRESS->value,
                            'quest_progress_id' => $questProgressId ?? $this->questProgress->id
                        ]
                );
    }

    /**
     *
     * @param int $questId
     * @param string $reason
     * @return int
     */
    private function endQuestPlayers(int $questId, string $reason): int {
        return QuestPlayer::updateAll(
                        [
                            'status' => AppStatus::LEFT->value,
                            'left_at' => time(),
                            'reason' => $reason],
                        [
                            'status' => [AppStatus::ONLINE->value, AppStatus::OFFLINE->value],
                            'quest__id' => $questId
                        ]
                );
    }

    /**
     *
     * @param int $questId
     * @return int
     */
    private function detachPlayersFromQuest(int $questId): int {
        return Player::updateAll(
                        ['quest_id' => null],
                        ['quest_id' => $questId]
                );
    }

    /**
     *
     * @param AppStatus $status
     * @return array{
     *     error: bool,
     *     msg: string
     * }
     */
    private function gameOver(AppStatus $status): array {
        // End the quest
        $this->quest->status = $status->value;
        $this->quest->completed_at = time();
        $this->save($this->quest);

        $message = "The quest {$this->quest->name} is over with status {$status->getLabel()}!!!";

        $this->endCurrentQuestProgress($status);
        $this->endQuestPlayers($this->quest->id, $message);
        $this->detachPlayersFromQuest($this->quest->id);

        $detail = [
            'status' => $status->getLabel(),
            'playerName' => $this->player->name,
            'questName' => $this->quest->name,
            'timestamp' => time(),
        ];

        $this->createQuestEvent('game-over', $message, $this->player, $detail);
        return ['error' => false, 'msg' => $message];
    }

    /**
     * Initialize the first QuestProgress when creating a new Quest
     *
     * @return bool
     */
    public function addFirstQuestProgress(): bool {

        $chapter = $this->quest->currentChapter;

        $questProgress = $this->addQuestProgress($chapter->first_mission_id);

        return ($questProgress !== null);
    }

    /**
     *
     * @param AppStatus|null $status
     * @return void
     */
    private function endCurrentQuestProgress(?AppStatus $status = AppStatus::TERMINATED): void {
        $questProgressId = $this->questProgress->id;

        $this->endCurrentTurn($questProgressId, $status);

        $this->questProgress->status = $status->value;
        $this->questProgress->completed_at = time();
        $this->save($this->questProgress);
    }

    /**
     * Add a QuestProgress model as an instance of a Mission model for a specific Quest
     *
     * @param int $missionId
     * @return QuestProgress|null
     * @throws \Exception
     */
    private function addQuestProgress(int $missionId): ?QuestProgress {
        $mission = Mission::findOne($missionId);
        if (!$mission) {
            throw new \Exception("Mission #{$missionId} not found");
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
    private function newQuestProgress(Mission &$mission, int $nextPlayerId): QuestProgress {

        $narrative = new NarrativeComponent(['mission' => $mission]);

        $questProgress = new QuestProgress([
            'quest_id' => $this->quest->id,
            'mission_id' => $mission->id,
            'current_player_id' => $nextPlayerId,
            'description' => $narrative->renderDescription(),
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        $this->save($questProgress);

        // Update component context
        $this->questProgress = $questProgress;

        return $questProgress;
    }

    /**
     *
     * @return int|null
     */
    private function getFirstMissionIdInNextChapter(): ?int {
        $currentChapterNumber = $this->questProgress->mission->chapter->chapter_number;
        $nextChapter = Chapter::find()
                ->where(['>', 'chapter_number', $currentChapterNumber])
                ->orderBy(['chapter_number' => SORT_DESC])
                ->one();

        // Return NULL if no more chapter in the story
        return $nextChapter?->first_mission_id;
    }

    /**
     *
     * @return int|null
     */
    private function getNextDefaultMissionId(): ?int {
        $nextMissionInChapter = Mission::find()
                ->where(['>', 'id', $this->questProgress->mission_id])
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
     * @return array<string, mixed>
     */
    private function getNextMissionDetail(QuestProgress &$currentQuestProgress, QuestProgress &$nextQuestProgress): array {
        $currentMission = $currentQuestProgress->mission;
        $nextMission = $nextQuestProgress->mission;

        $currentPlayer = $currentQuestProgress->currentPlayer;
        $nextPlayer = $nextQuestProgress->currentPlayer;

        $detail = [
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

        return $detail;
    }

    /**
     *
     * @param int $nextMissionId
     * @return array{
     *     error: bool,
     *     msg: string
     * }
     */
    private function setNextMission(int $nextMissionId): array {
        Yii::debug("*** debug *** setNextMission nextMissionId={$nextMissionId}");

        $currentQuestProgress = $this->questProgress;
        $currentPlayer = $this->quest->currentPlayer;
        $this->endCurrentQuestProgress();
        $nextQuestProgress = $this->addQuestProgress($nextMissionId);

        $detail = $this->getNextMissionDetail($currentQuestProgress, $nextQuestProgress);
        $message = "The mission '{$detail['currentMissionName']}' is over with, let's move to the next mission '{$detail['nextMissionName']}'!!!";

        $this->createQuestEvent('next-mission', $message, $currentPlayer, $detail);

        return ['error' => false, 'msg' => $message];
    }

    /**
     *
     * @param int|null $nextMissionId
     * @return array{
     *     error: bool,
     *     msg: string
     * }
     */
    public function moveToNextMission(?int $nextMissionId = null): array {
        Yii::debug("*** debug *** moveToNextMission nextMissionId=" . ($nextMissionId ? $nextMissionId : 'null'));

        if ($nextMissionId) {
            return $this->setNextMission($nextMissionId);
        }

        $nextDefaultMissionId = $this->getNextDefaultMissionId();
        if ($nextDefaultMissionId) {
            return $this->moveToNextMission($nextDefaultMissionId);
        }
        // We have reached the end of the game
        return $this->gameOver(AppStatus::COMPLETED);
    }

    /**
     *
     * @return array{
     *     error: bool,
     *     msg: string
     * }
     */
    public function nextPlayer(): array {
        $this->endCurrentTurn();

        $currentPlayer = $this->player;
        $questTurn = $this->setNextQuestTurn();

        if (!$questTurn) {
            // No next possible player, game over
            return $this->gameOver(AppStatus::ABORTED);
        }

        // setNextQuestTurn has changed $this->player to the next player
        $nextPlayer = $this->player;

        $message = "Move to next player";
        $detail = [
            'currentPlayerId' => $currentPlayer->id,
            'currentPlayerName' => $currentPlayer->name,
            'questProgressId' => $this->questProgress->id,
            'nextPlayerId' => $nextPlayer->id,
            'nextPlayerName' => $nextPlayer->name,
            'nextTurnSequence' => $this->nextSequence,
            'timestamp' => time(),
        ];

        $this->createQuestEvent('next-turn', $message, $currentPlayer, $detail);
        return ['error' => false, 'msg' => $message];
    }

    /**
     *
     * @param string $eventType
     * @param string $eventDescription
     * @param Player|null $initiator
     * @param array<string, mixed> $detail
     * @return bool
     * @throws \Exception
     */
    private function createQuestEvent(string $eventType, string $eventDescription, ?Player $initiator, array $detail = []): bool {
        Yii::debug("*** debug *** createQuestEvent - eventType={$eventType}, eventDescription={$eventDescription} initiator={$initiator->name}, detail=" . print_r($detail, true));
        try {
            $sessionId = Yii::$app->session->get('sessionId');
            $player = $initiator ?? $this->player;
            $quest = $this->quest;

            $data['action'] = $eventDescription;
            $data['detail'] = $detail;

            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            $errorMessage = "Error: " . $e->getMessage() . "<br />Stack Trace:<br />" . nl2br($e->getTraceAsString());
            throw new \Exception($errorMessage);
        }
    }
}
