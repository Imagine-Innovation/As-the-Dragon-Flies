<?php

namespace common\components;

use common\components\AppStatus;
use common\models\Action;
use common\models\ActionFlow;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestProgress;
use Yii;
use yii\base\Component;

class QuestComponent extends Component
{

    public Quest $quest;

    public function __construct($config = []) {
        $this->quest = null;

        // Call the parent's constructor
        parent::__construct($config);

        if (!$this->quest) {
            throw new \yii\web\NotFoundHttpException("The quest you are looking for does not exist.");
        }
    }

    public function xxinitQuestProgress(int $questId) {

        $quest = $this->findQuest($questId);
        $chapter = $quest->currentChapter;

        $questProgress = $this->initProgress($questId, $chapter->first_mission_id);
        $this->addQuestActions($questProgress->id, $chapter->first_mission_id);
    }

    public function initQuestProgress() {

        $chapter = $this->quest->currentChapter;

        $questProgress = $this->initProgress($this->quest->id, $chapter->first_mission_id);
        $this->addQuestActions($questProgress->id, $chapter->first_mission_id);
    }

    public function initProgress(int $questId, int $missionId): QuestProgress {
        $questProgress = new QuestProgress([
            'quest_id' => $questId,
            'mission_id' => $missionId,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        if (!$questProgress->save()) {
            throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($questProgress->errors, 0, false)));
        }
        return $questProgress;
    }

    private function isPrequisiteFulfilled(ActionFlow &$prerequisite, int $questProgressId): bool {
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

    private function isEligible(Action &$action, int $questProgressId): bool {
        foreach ($action->prerequisites as $prerequisite) {
            $eligible = $this->isPrequisiteFulfilled($prerequisite, $action->id, $questProgressId);

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
            throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($questAction->errors, 0, false)));
        }
    }

    public function addQuestActions(int $questProgressId, int $missionId) {
        $actions = Action::findAll(['mission_id' => $missionId]);

        foreach ($actions as $action) {
            if ($this->isEligible($action, $questProgressId)) {
                $this->addQuestAction($action->id, $questProgressId);
            }
        }
    }

    protected function findQuest(int $id): Quest {
        $quest = Quest::findOne(['id' => $id]);

        if ($quest) {
            return $quest;
        }

        throw new \yii\web\NotFoundHttpException("The quest (id={$id}) you are looking for does not exist.");
    }
}
