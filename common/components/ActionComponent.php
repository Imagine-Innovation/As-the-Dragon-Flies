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
use common\helpers\DiceRoller;
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

        Yii::debug("*** debug *** ActionComponent.__construct ");
        // Align the context data from QuestAction
        if ($this->questAction) {
            $this->questProgress = $this->questAction->questProgress;
        }

        if (!$this->questProgress) {
            throw new \Exception("Missing QuestProgress!!!");
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

    protected function determineActionStatus(): AppStatus {
        $dc = $this->action->dc;
        $partialDc = $this->action->partial_dc;

        $diceToRoll = "1d20+{$this->getModifier()}";
        $diceRoll = DiceRoller::roll($diceToRoll);

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

        $this->save($this->questAction);
    }

    protected function unlockNextActions(AppStatus $status): array {
        $triggeredActions = $this->action->triggers;

        $unlockedQuestActions = [];
        $questProgressId = $this->questProgress->id;
        foreach ($triggeredActions as $actionFlow) {
            // Bitwise comparison between actual status and expected status to unlock next action
            if (
                    ($actionFlow->status & $status->value) &&
                    $this->isActionEligible($actionFlow->nextAction, $questProgressId)
            ) {
                $actionId = $actionFlow->next_action_id;
                $unlockedQuestActions[] = $this->addQuestAction($actionId, $questProgressId);
            }
        }
        return $unlockedQuestActions;
    }

    protected function updatePlayerStats(?int $gainedXp, ?string $hpLossDice) {
        If (!$this->player) {
            return;
        }

        $this->player->experience_points += $gainedXp ?? 0;

        $hpLoss = DiceRoller::roll($hpLossDice);
        if ($this->player->hit_points >= ($hpLoss ?? 0)) {
            $this->player->hit_points -= $gainedXp ?? 0;
        } else {
            $this->player->hit_points = 0;
        }
        $this->save($this->player);
    }

    protected function registerGainsAndLosses(AppStatus $status): ?int {
        $outcomes = Outcome::findAll(['action_id' => $this->action->id]);

        if (!$outcomes) {
            // nothing to register
            return null;
        }

        $nextMissionId = null;
        foreach ($outcomes as $outcome) {
            if ($outcome->status & $status->value) {
                $nextMissionId = $outcome->next_mission_id;
                $this->updatePlayerStats($outcome->gained_xp, $outcome->hp_loss_dice);

                $this->player->addCoins($outcome->gained_gp, 'gp');
                $this->player->addItems($outcome->item_id);
            }
        }
        return $nextMissionId;
    }

    public function evaluateActionOutcome(): array {
        if (!$this->action) {
            throw new \Exception("Action not found.");
        }

        $status = $this->determineActionStatus();
        $this->endCurrentAction($status);
        $nextActions = $this->unlockNextActions($status);

        // Get outcome details
        $nextMissionId = $this->registerGainsAndLosses($status);

        return [
            'status' => $status,
            'nextMissionId' => $nextMissionId,
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

        $this->save($questAction);
        return $questAction;
    }

    protected function save(\yii\db\ActiveRecord $model) {

        if (!$model->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($model->errors, 0, false)));
        }
    }
}
