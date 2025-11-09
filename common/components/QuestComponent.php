<?php

namespace common\components;

use common\components\AppStatus;
use common\components\NarrativeComponent;
use common\models\Mission;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Yii;
use yii\base\Component;
use Yii\helpers\ArrayHelper;

class QuestComponent extends Component
{

    public ?Quest $quest = null;
    public ?QuestProgress $questProgress = null;

    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        if ($this->questProgress) {
            $this->quest = $this->questProgress->quest;
        }
    }

    private function getNextQuestPlayer(): ?QuestPlayer {
        // If the current player is defined, search for the next active player
        $currentQuestPlayer = $this?->quest?->currentQuestPlayer;

        if (!$currentQuestPlayer) {
            return null;
        }
        $currentTurn = $currentQuestPlayer->player_turn;

        // Find the next player
        return QuestPlayer::find()
                        ->where(['quest_id' => $this->quest->id])
                        ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                        ->andWhere(['>', 'player_turn', $currentTurn])
                        ->orderBy(['player_turn' => SORT_ASC])
                        ->one();
    }

    private function setQuestCurrentPlayerId(int $playerId) {
        Quest::updateAll(
                ['current_player_id' => $playerId],
                ['id' => $this->quest->id]
        );
    }

    private function getNextPlayerId(): ?int {

        $nextQuestPlayer = $this->getNextQuestPlayer();

        // If no current player or no next player found,
        // loop back to the first active player
        if (!$nextQuestPlayer) {
            $nextQuestPlayer = QuestPlayer::find()
                    ->where(['quest_id' => $this->quest->id])
                    ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                    ->orderBy(['player_turn' => SORT_ASC])
                    ->one();
        }
        $nextPlayerId = $nextQuestPlayer?->player_id;

        $this->setQuestCurrentPlayerId($nextPlayerId);

        Yii::debug("*** debug *** getNextPlayerId returns {$nextPlayerId}");
        return $nextPlayerId;
    }

    private function getLastTurnSequence(): int {

        $questProgressId = $this->questProgress->id;

        $nextSequence = QuestTurn::find()
                ->where(['quest_progress_id' => $questProgressId])
                ->max('sequence');

        return $nextSequence ?? 0;
    }

    private function setNextQuestTurn(): ?QuestTurn {
        $nextPlayerId = $this->getNextPlayerId();

        // The quest has no more active players, no new turns can be added.
        if (!$nextPlayerId) {
            return null;
        }

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

    private function endCurrentTurn() {
        $questProgressId = $this->questProgress->id;
        QuestTurn::updateAll(
                ['status' => AppStatus::TERMINATED->value],
                ['status' => AppStatus::IN_PROGRESS->value, 'quest_progress_id' => $questProgressId]
        );
    }

    private function gameOver(AppStatus $status) {

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

    private function endCurrentQuestProgress() {
        $questProgressId = $this->questProgress->id;

        $this->endCurrentTurn($questProgressId);

        QuestProgress::updateAll(
                [
                    'status' => AppStatus::TERMINATED->value,
                    'completed_at' => time()
                ],
                ['quest_progress_id' => $questProgressId]
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

        $nextPlayerId = $this->getNextPlayerId();
        if (!$nextPlayerId) {
            // No more active player: game over
            $this->gameOver(AppStatus::FAILURE);
            return null;
        }

        $questProgress = $this->newQuestProgress($mission, $nextPlayerId);

        // Update component context
        $this->questProgress = $questProgress;

        $actionComponent = new ActionComponent(['questProgress' => $questProgress]);
        $actionComponent->addQuestActions($$missionId);

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
                ->orderBy(['id' => SORT_DESC])
                ->one();

        if ($nextMissionInChapter) {
            return $nextMissionInChapter->id;
        }
        return $this->getFirstMissionIdInNextChapter();
    }

    public function moveToNextMission(array $param, ?int $nextMissionId = null): array {
        Yii::debug("*** debug *** moveToNextMission param=" . print_r($param, true) . ", nextMissionId=" . ($nextMissionId ? $nextMissionId : 'null'));

        if ($nextMissionId) {
            $this->endCurrentQuestProgress();
            $newQuestProgress = $this->addQuestProgress($nextMissionId);
            // update the component context
            $this->questProgress = $newQuestProgress;

            return [];
        }

        $nextDefaultMissionId = $this->getNextDefaultMissionId();
        if ($nextDefaultMissionId === null) {
            $this->gameOver(AppStatus::COMPLETED);
        }

        return [];
    }

    public function nextPlayer(): array {
        $this->endCurrentTurn();

        $questTurn = $this->setNextQuestTurn();

        if ($questTurn) {
            return ['error' => false, 'msg' => ''];
        }
        $this->gameOver(AppStatus::COMPLETED);
        return ['error' => true, 'msg' => 'Next player turn not found'];
    }

    private function createQuestEvent(string $eventType, string $actionName, array $questFlow = []): bool {
        $sessionId = Yii::$app->session->get('sessionId');
        try {
            $player = $this->quest->currentPlayer;
            $quest = $this->quest;
            $data['action'] = $actionName;
            $data['detail'] = [
                'status' => $questFlow['status'],
                'nextPlayerId' => $questFlow['nextPlayerId'],
                'mission' => $questFlow['mission'],
                'nextMission' => $questFlow['nextMission'],
            ];
            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($e, 0, false)));
        }
    }
}
