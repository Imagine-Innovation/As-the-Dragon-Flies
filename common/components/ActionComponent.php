<?php

namespace common\components;

use common\components\AppStatus;
use common\models\Action;
use common\models\ActionFlow;
use common\models\ActionTypeSkill;
use common\models\Player;
use common\models\PlayerSkill;
use common\models\QuestAction;
use common\models\QuestProgress;
use common\models\Outcome;
use Yii;
use yii\base\Component;
use Yii\helpers\ArrayHelper;

class ActionComponent extends Component
{

    // Context data
    public ?Action $action = null;
    public ?QuestAction $questAction = null;
    public ?QuestProgress $questProgress = null;
    public ?Player $player = null;

    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        // Align the context data from QuestAction
        if ($this->questAction) {
            $this->questProgress = $this->questAction->questProgress;
        }

        if (!$this->questAction || !$this->questProgress) {
            //throw new \Exception("Missing QuestAction ot QuestProgress!!!");
        }

        $this->action ??= $this->questAction?->action;
        $this->player ??= $this->questProgress?->currentPlayer;
    }

    /*
      protected function getModifier(int $actionId, int $playerId): int {
      $bonuses = Action::find()
      ->select('ps.bonus')
      ->from(['a' => Action::tableName()])
      ->innerJoin(['atp' => ActionType::tableName()], 'atp.id = a.action_type_id')
      ->innerJoin(['ats' => ActionTypeSkill::tableName()], 'ats.action_type_id = atp.id')
      ->innerJoin(['ps' => PlayerSkill::tableName()], 'ats.skill_id = ps.skill_id')
      ->where(['ps.player_id' => $playerId, 'a.id' => $actionId])
      ->column();

      return !empty($bonuses) ? max($bonuses) : 0;
      }
     *
     */

    protected function getModifier(): int {
        $skillIds = ActionTypeSkill::find()
                ->select('skill_id')
                ->where(['action_type_id' => $this->action->action_type_id])
                ->column();

        if (!$skillIds) {
            // No skill required, no modifier to apply
            return 0;
        }

        $bonuses = PlayerSkill::find()
                ->select('bonus')
                ->where(['player_id' => $this->player->id, 'skill_id' => $skillIds])
                ->column();

        return !$bonuses ? max($bonuses) : 0;
    }

    protected function determineStatus(int $dc, ?int $partialDc): AppStatus {
        $d20 = random_int(1, 20);
        $diceRoll = $d20 + $this->getModifier();

        if ($diceRoll >= $dc) {
            return AppStatus::SUCCESS;
        }

        if ($partialDc !== null && $diceRoll >= $partialDc) {
            return AppStatus::PARTIAL;
        }

        return AppStatus::FAILURE;
    }

    protected function endCurrentAction(AppStatus $status): void {
        $this->questAction->status = $status->value;
        if ($this->action->is_single_action) {
            $this->questAction->eligible = false;
        }

        if (!$this->questAction->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($this->questAction->errors, 0, false)));
        }
    }

    protected function unlockNextActions(AppStatus $status): array {
        $triggeredActions = $this->action->triggers;

        $unlockedQuestActions = [];
        foreach ($triggeredActions as $actionFlow) {
            if (($actionFlow->status & $status->value) && $this->isActionEligible($actionFlow->nextAction, $$this->questProgress->id)) {
                $unlockedQuestActions[] = $this->addQuestAction($actionFlow->next_action_id, $this->questProgress->id);
            }
        }
        return $unlockedQuestActions;
    }

    public function evaluateAction(): array {
        if (!$this->action) {
            throw new \Exception("Action not found.");
        }

        // Determine outcome status
        $status = $this->determineStatus($this->action->dc, $this->action->partial_dc);

        $this->endCurrentAction($status);

        // Get follow-up actions
        $nextActions = $this->unlockNextActions($status);

        // Get outcome details
        $outcome = $this->getOutcome($status);

        return [
            'status' => $status,
            'nextActions' => $nextActions,
            'outcome' => $outcome,
        ];
    }

    protected function isActionPrerequisiteMet(ActionFlow &$prerequisite, int $questProgressId): bool {
        $questAction = QuestAction::findOne([
            'quest_progress_id' => $questProgressId,
            'action_id' => $prerequisite->previous_action_id
        ]);

        if ($questAction) {
            $questActionStatus = $questAction->status;
            $criterionMask = $prerequisite->status;

            // Bitwise comparison
            return ($questActionStatus & $criterionMask) === $questActionStatus;
        }
        // No prerequisite action found, prerequisite not met by default
        return false;
    }

    public function isActionEligible(Action &$action, int $questProgressId): bool {
        foreach ($action->prerequisites as $prerequisite) {
            $eligible = $this->isActionPrerequisiteMet($prerequisite, $action->id, $questProgressId);

            if (!$eligible) {
                // If at least one prerequisite is not met, do not continue.
                return false;
            }
        }
        return true;
    }

    public function addQuestAction(int $actionId, int $questProgressId): QuestAction {
        $questAction = QuestAction::findOne([
            'action_id' => $actionId,
            'quest_progress_id' => $questProgressId
        ]);

        if ($questAction) {
            $questAction->status = null;
            $questAction->eligible = true;
        } else {
            $questAction = new QuestAction([
                'quest_progress_id' => $questProgressId,
                'action_id' => $actionId
            ]);
        }

        if (!$questAction->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questAction->errors, 0, false)));
        }
        return $questAction;
    }
}
