<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\NarrativeComponent;
use common\models\Mission;
use common\models\Player;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Yii;
use Yii\helpers\ArrayHelper;
use yii\web\Request;

class QuestManager extends BaseManager
{

    // Context data
    // Public facade
    public ?Quest $quest = null;
    public ?QuestProgress $questProgress = null;
    // internal use
    private ?Player $player = null;

    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        if ($this->questProgress) {
            $this->quest = $this->questProgress->quest;
        }
        $this->player ??= $this->quest?->currentPlayer;
    }

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

    private function setQuestCurrentPlayerId(int $playerId) {
        Quest::updateAll(
                ['current_player_id' => $playerId],
                ['id' => $this->quest->id]
        );

        // Update component context
        $this->player ??= $this->quest?->currentPlayer;
    }

    private function getLastTurnSequence(): int {

        $questProgressId = $this->questProgress->id;

        $nextSequence = QuestTurn::find()
                ->where(['quest_progress_id' => $questProgressId])
                ->max('sequence');

        return $nextSequence ?? 0;
    }

    private function setNextQuestTurn(): ?QuestTurn {
        $nextQuestPlayer = $this->getNextQuestPlayer();
        $nextPlayerId = $nextQuestPlayer?->player_id;

        // The quest has no more active players, no new turns can be added.
        if (!$nextPlayerId) {
            return null;
        }

        $this->setQuestCurrentPlayerId($nextPlayerId);

        $nextSequence = $this->getLastTurnSequence($this->questProgress->id) + 1;

        $questTurn = new QuestTurn([
            'player_id' => $nextPlayerId,
            'quest_progress_id' => $this->questProgress->id,
            'sequence' => $nextSequence,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time()
        ]);

        if (!$questTurn->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questTurn->errors, 0, false)));
        }
        return $questTurn;
    }

    private function endCurrentTurn(?int $questProgressId = null, ?AppStatus $status = AppStatus::TERMINATED) {
        QuestTurn::updateAll(
                ['status' => $status->value],
                ['status' => AppStatus::IN_PROGRESS->value, 'quest_progress_id' => $questProgressId ?? $this->questProgress->id]
        );
    }

    private function gameOver(AppStatus $status) {
        // End the quest
        Quest::updateAll(
                ['status' => $status->value, 'completed_at' => time()],
                ['id' => $this->quest->id]
        );
        $this->endCurrentQuestProgress($status);

        $message = "The quest {$this->quest->name} is over with status {$status->name}!!!";

        $detail = [
            'status' => $status,
            'playerName' => $this->player->name,
            'questName' => $this->quest->name,
            'timestamp' => time(),
        ];

        $this->createQuestEvent('game-over', $message, $detail);
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

    private function endCurrentQuestProgress(?AppStatus $status = AppStatus::TERMINATED) {
        $questProgressId = $this->questProgress->id;

        $this->endCurrentTurn($questProgressId, $status);

        QuestProgress::updateAll(
                [
                    'status' => $status->value,
                    'completed_at' => time()
                ],
                ['id' => $questProgressId]
        );
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

    private function newQuestProgress(Mission &$mission, int $nextPlayerId): ?QuestProgress {

        $narrative = new NarrativeComponent(['mission' => $mission]);

        $questProgress = new QuestProgress([
            'quest_id' => $this->quest->id,
            'mission_id' => $mission->id,
            'current_player_id' => $nextPlayerId,
            'description' => $narrative->renderDescription(),
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        if (!$questProgress->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questProgress->errors, 0, false)));
        }
        // Update component context
        $this->questProgress = $questProgress;

        return $questProgress;
    }

    private function getFirstMissionIdInNextChapter(): ?int {
        $currentChapterNumber = $this->questProgress->mission->chapter->chapter_number;
        $nextChapter = Chapter::find()
                ->where(['>', 'chapter_number', $currentChapterNumber])
                ->orderBy(['chapter_number' => SORT_DESC])
                ->one();

        // Return NULL if no more chapter in the story
        return $nextChapter?->first_mission_id;
    }

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

    private function getNextMissionDetail(int $nextMissionId, QuestProgress &$newQuestProgress): array {
        $nextMission = Mission::findOne($nextMissionId);

        $nextPlayer = $newQuestProgress->currentPlayer;

        $detail = [
            'missionId' => $nextMission->id,
            'missionName' => $nextMission->name,
            'nextPlayerId' => $nextPlayer->id,
            'nextPlayerName' => $nextPlayer->name,
            'timestamp' => time(),
        ];

        return $detail;
    }

    private function setNextMission(int $nextMissionId): array {
        Yii::debug("*** debug *** setNextMission nextMissionId={$nextMissionId}");

        $this->endCurrentQuestProgress();
        $newQuestProgress = $this->addQuestProgress($nextMissionId);

        $message = "The mission '{$this->questProgress->name}' is over with, let's move to the next mission '{$newQuestProgress->name}'!!!";
        $detail = $this->getNextMissionDetail($nextMissionId, $newQuestProgress);

        $this->createQuestEvent('next-mission', $message, $detail);

        return ['error' => false, 'msg' => $message];
    }

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

    public function nextPlayer(): array {
        $this->endCurrentTurn();

        $currentPlayer = $this->quest->currentPlayer;
        $questTurn = $this->setNextQuestTurn();

        if (!$questTurn) {
            // No next possible player, game over
            return $this->gameOver(AppStatus::ABORTED);
        }

        $nextPlayer = $this->quest->currentPlayer;

        $message = "Move to next player";
        $detail = [
            'currentPlayerId' => $currentPlayer->id,
            'currentPlayerName' => $currentPlayer->name,
            'nextPlayerId' => $nextPlayer->id,
            'nextPlayerName' => $nextPlayer->name,
            'timestamp' => time(),
        ];

        $this->createQuestEvent('next-turn', $message, $detail);
        return ['error' => false, 'msg' => $message];
    }

    private function createQuestEvent(string $eventType, string $eventDescription, array $detail = []): bool {
        try {
            $sessionId = Yii::$app->session->get('sessionId');
            $player = $this->quest->currentPlayer;
            $quest = $this->quest;

            $data['action'] = $eventDescription;
            $data['detail'] = $detail;

            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($e, 0, false)));
        }
    }
}
