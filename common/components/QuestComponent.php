<?php

namespace common\components;

use common\components\AppStatus;
use common\components\NarrativeComponent;
use common\models\Action;
use common\models\ActionFlow;
use common\models\Mission;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Yii;
use yii\base\Component;
use Yii\helpers\ArrayHelper;

class QuestComponent extends Component
{

    public Quest $quest;

    public function __construct($config = []) {
        //$this->quest = null;
        // Call the parent's constructor
        parent::__construct($config);

        if (!$this->quest) {
            throw new \yii\web\NotFoundHttpException("The quest you are looking for does not exist.");
        }
    }

    public function initQuestProgress(): bool {

        $chapter = $this->quest->currentChapter;

        $questProgress = $this->addQuestProgress($chapter->first_mission_id, $this->quest->initiator_id);
        $this->addQuestActions($questProgress->id, $chapter->first_mission_id);
        $this->endCurrentTurn($questProgress->id);
        $newQuestTurn = $this->addNewTurn($questProgress);

        return ($newQuestTurn !== null);
    }

    private function getNextPlayerId(QuestPlayer &$currentQuestPlayer): int|null {
        $currentTurn = $currentQuestPlayer->player_turn;

        // Find the next player
        $nextPlayer = QuestPlayer::find()
                ->where(['quest_id' => $this->quest->id, 'left_at' => null])
                ->andWhere(['>', 'player_turn', $currentTurn])
                ->orderBy(['player_turn' => SORT_ASC])
                ->one();

        // If no next player, loop back to the first active player
        if (!$nextPlayer) {
            $nextPlayer = QuestPlayer::find()
                    ->where(['quest_id' => $this->quest->id, 'left_at' => null])
                    ->orderBy(['player_turn' => SORT_ASC])
                    ->one();
        }
        return $nextPlayer?->player_id;
    }

    public function addNewTurn(QuestProgress &$questProgress): QuestTurn|null {
        $currentQuestPlayer = $questProgress->questPlayer;
        $nextPlayerId = $this->getNextPlayerId($currentQuestPlayer);

        // The quest has no more active players, no new turns can be added.
        if (!$nextPlayerId) {
            return null;
        }
        $questTurn = new QuestTurn([
            'player_id' => $nextPlayerId,
            'quest_progress_id' => $questProgress->id,
            'sequence' => 1,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time()
        ]);
        if (!$questTurn->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questTurn->errors, 0, false)));
        }
        return $questTurn;
    }

    public function endCurrentTurn(int $questProgressId) {
        QuestTurn::updateAll([
            ['status' => AppStatus::TERMINATED->value],
            ['quest_progress_id' => $questProgressId, 'status' => AppStatus::IN_PROGRESS->value]
        ]);
    }

    public function addQuestProgress(int $missionId, int $initiatorId): QuestProgress {
        $mission = Mission::findOne($missionId);
        $narrative = new NarrativeComponent(['mission' => $mission]);
        $questProgress = new QuestProgress([
            'quest_id' => $this->quest->id,
            'mission_id' => $missionId,
            'current_player_id' => $initiatorId,
            'description' => $narrative->renderDescription(),
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        if (!$questProgress->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questProgress->errors, 0, false)));
        }
        return $questProgress;
    }

    private function isActionPrequisiteFulfilled(ActionFlow &$prerequisite, int $questProgressId): bool {
        $questAction = QuestAction::findOne([
            'quest_progress_id' => $questProgressId,
            'action_id' => $prerequisite->previous_action_id
        ]);

        if ($questAction) {
            $outcome = $questAction->status;
            $criterionMask = $prerequisite->status;

            // Bitwise comparison
            return ($outcome & $criterionMask) === $outcome;
        }
        // No prerequisite action found, prerequisite not met
        return false;
    }

    private function isActionEligible(Action &$action, int $questProgressId): bool {
        foreach ($action->prerequisites as $prerequisite) {
            $eligible = $this->isActionPrequisiteFulfilled($prerequisite, $action->id, $questProgressId);

            if (!$eligible) {
                // If at least one prerequisite is not met, do not continue.
                return false;
            }
        }
        return true;
    }

    private function addQuestAction(int $actionId, int $questProgressId) {
        $questAction = new QuestAction([
            'quest_progress_id' => $questProgressId,
            'action_id' => $actionId
        ]);

        if (!$questAction->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questAction->errors, 0, false)));
        }
    }

    public function addQuestActions(int $questProgressId, int $missionId) {
        $actions = Action::findAll(['mission_id' => $missionId]);

        foreach ($actions as $action) {
            if ($this->isActionEligible($action, $questProgressId)) {
                $this->addQuestAction($action->id, $questProgressId);
            }
        }
    }
}
