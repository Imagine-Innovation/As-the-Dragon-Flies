<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\gameplay\BaseManager;
use common\helpers\DiceRoller;
use common\models\Action;
use common\models\ActionFlow;
use common\models\ActionTypeSkill;
use common\models\Outcome;
use common\models\Player;
use common\models\PlayerSkill;
use common\models\QuestAction;
use common\models\QuestProgress;
use Yii;

class ActionManager extends BaseManager
{

    // Context data
    // Public facade
    public ?QuestAction $questAction = null;
    public ?QuestProgress $questProgress = null;
    // internal use
    private ?Action $action = null;
    private ?Player $player = null;
    private ?int $nextMissionId = null;
    private int $hpLoss = 0;

    /**
     *
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        // Align the context data from QuestAction
        if ($this->questAction) {
            $this->questProgress = $this->questAction->questProgress;
        }

        if (!$this->questProgress) {
            throw new \Exception("Missing QuestProgress!!!");
        }

        $this->action ??= $this->questAction?->action;
        $this->player ??= $this->questProgress->currentPlayer;
    }

    /**
     *
     * @return int
     */
    private function getModifier(): int {
        Yii::debug("*** debug *** getModifier - action={$this->action?->name}, player={$this->player?->name}");
        $skillIds = ActionTypeSkill::find()
                ->select('skill_id')
                ->where(['action_type_id' => $this->action?->action_type_id])
                ->column();

        if (!$skillIds) {
            // No skill required, no modifier to apply
            return 0;
        }

        $modifier = PlayerSkill::find()
                ->where(['player_id' => $this->player?->id, 'skill_id' => $skillIds])
                ->max('bonus');

        return is_numeric($modifier) ? (int) $modifier : 0;
    }

    /**
     *
     * @param int $diceRoll
     * @return AppStatus
     */
    private function determineActionStatus(int $diceRoll): AppStatus {
        Yii::debug("*** debug *** determineActionStatus - action={$this->action?->name}, diceRoll={$diceRoll}");
        $dc = $this->action?->dc;
        $partialDc = $this->action?->partial_dc;

        if ($diceRoll >= $dc) {
            return AppStatus::SUCCESS;
        }

        if ($partialDc !== null && $diceRoll >= $partialDc) {
            return AppStatus::PARTIAL;
        }

        return AppStatus::FAILURE;
    }

    /**
     *
     * @param AppStatus $status
     * @param bool|null $canReplay
     * @return void
     */
    private function endCurrentAction(AppStatus $status, ?bool $canReplay = true): void {
        Yii::debug("*** debug *** endCurrentAction - action={$this->action?->name}, status={$status->getLabel()}");

        QuestAction::updateAll(
                ['status' => $status->value, 'eligible' => $canReplay],
                ['action_id' => $this->questAction?->action_id, 'quest_progress_id' => $this->questAction?->quest_progress_id]
        );
    }

    /**
     *
     * @param AppStatus $status
     * @return QuestAction[]
     */
    private function unlockNextActions(AppStatus $status): array {
        Yii::debug("*** debug *** unlockNextActions - action={$this->action?->name}, status={$status->getLabel()}");
        $unlockedQuestActions = [];
        $triggeredActions = $this->action?->triggers;

        if ($triggeredActions === null) {
            return $unlockedQuestActions;
        }

        $questProgressId = (int) $this->questProgress?->id;
        /** @var ActionFlow $actionFlow */
        foreach ($triggeredActions as $actionFlow) {
            // Bitwise comparison between actual status and expected status to unlock next action
            $bitwiseComparison = ($actionFlow->status & $status->value);
            $isEligible = $this->isActionEligible($actionFlow->nextAction, $questProgressId);
            if ($bitwiseComparison && $isEligible) {
                $actionId = $actionFlow->next_action_id;
                $unlockedQuestActions[] = $this->addOneQuestAction($actionId, $questProgressId);
            }
        }
        Yii::debug("*** debug *** unlockNextActions - isEligible: " . count($unlockedQuestActions) . " triggered action(s)");
        return $unlockedQuestActions;
    }

    /**
     *
     * @param AppStatus $status
     * @return Outcome[]
     */
    private function getOutcomes(AppStatus $status): array {
        Yii::debug("*** debug *** getOutcomes - status={$status->getLabel()}");
        $outcomes = Outcome::findAll(['action_id' => $this->action?->id]);

        if (!$outcomes) {
            // nothing to register
            return [];
        }

        $selectedOutcomes = [];
        foreach ($outcomes as $outcome) {
            $bitwiseComparison = ($outcome->status & $status->value);
            Yii::debug("*** debug *** getOutcomes - outcome->status={$outcome->status}, status->value={$status->value}, bitwiseComparison={$bitwiseComparison}");

            if ($bitwiseComparison) {
                $selectedOutcomes[] = $outcome;
            }
        }
        Yii::debug("*** debug *** getOutcomes - selectedOutcomes=" . print_r($selectedOutcomes, true));

        return $selectedOutcomes;
    }

    /**
     *
     * @param Outcome[] $outcomes
     * @return bool
     */
    private function canReplay(array $outcomes): bool {
        Yii::debug("*** debug *** canReplay - outcomes=" . count($outcomes));

        if (empty($outcomes)) {
            // nothing to register
            return false;
        }

        $nextMissionId = null;
        $canReplay = true;
        foreach ($outcomes as $outcome) {
            $canReplay = $canReplay && ($outcome->can_replay === 1);
            $nextMissionId = ($outcome->next_mission_id === $this->questProgress?->mission_id) ? null : $outcome->next_mission_id;
        }
        $this->nextMissionId = $nextMissionId;
        return $canReplay;
    }

    /**
     *
     * @param AppStatus $status
     * @param Outcome[] $outcomes
     * @param string $diceRollLabel
     * @param bool $canReplay
     * @return array<string, mixed>
     */
    private function returnOutcomeEvaluation(AppStatus &$status, array $outcomes, string $diceRollLabel, bool $canReplay): array {
        $missionId = $this->questProgress?->mission_id;
        return [
            'action' => $this->action,
            'status' => $status,
            'outcomes' => $outcomes,
            'diceRoll' => $diceRollLabel,
            'hpLoss' => $this->hpLoss,
            'isFree' => $this->action?->is_free,
            'canReplay' => $canReplay,
            'questProgressId' => $this->questProgress?->id,
            'missionId' => $missionId,
            'nextMissionId' => $this->nextMissionId ?? $missionId,
        ];
    }

    /**
     *
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function evaluateActionOutcome(): array {
        Yii::debug("*** debug *** evaluateActionOutcome");
        if (!$this->action) {
            throw new \Exception("Action not found.");
        }

        $this->hpLoss = 0; // Resets the HP loss counter
        $modifier = $this->getModifier();
        $diceToRoll = $modifier ? "1d20+{$modifier}" : "d20";
        $diceRoll = DiceRoller::roll($diceToRoll);

        $status = $this->determineActionStatus($diceRoll);

        // Get outcome details
        $outcomes = $this->getOutcomes($status);
        $canReplay = $this->canReplay($outcomes);
        $this->endCurrentAction($status, $canReplay);
        $this->unlockNextActions($status);

        // Upate player stats
        $playerManager = new PlayerManager(['player' => $this->player]);
        $playerManager->registerGainsAndLosses($outcomes);

        return $this->returnOutcomeEvaluation($status, $outcomes, "Rolling {$diceToRoll} gave {$diceRoll}", $canReplay);
    }

    /**
     *
     * @param ActionFlow $prerequisite
     * @param int $questProgressId
     * @return bool
     */
    private function isActionPrerequisiteMet(ActionFlow &$prerequisite, int $questProgressId): bool {
        Yii::debug("*** debug *** isActionPrerequisiteMet - prequisite={$prerequisite->previousAction->name}, questProgressId={$questProgressId}");
        $questAction = QuestAction::findOne([
            'quest_progress_id' => $questProgressId,
            'action_id' => $prerequisite->previous_action_id
        ]);

        if ($questAction) {
            $questActionStatus = $questAction->status;
            $criterionMask = $prerequisite->status;
            $bitwiseComparison = ($questActionStatus & $criterionMask);
            Yii::debug("*** debug *** isActionPrerequisiteMet - criterionMask={$criterionMask}, questActionStatus={$questActionStatus}, bitwiseComparison={$bitwiseComparison}");

            // Bitwise comparison
            return $bitwiseComparison === $questActionStatus;
        }
        Yii::debug("*** debug *** isActionPrerequisiteMet - questAction not found");
        // No prerequisite action found, prerequisite not met by default
        return false;
    }

    /**
     *
     * @param Action $action
     * @param int $questProgressId
     * @return bool
     */
    private function isActionEligible(Action &$action, int $questProgressId): bool {
        Yii::debug("*** debug *** isActionEligible - action={$action->name}, questProgressId={$questProgressId}");
        foreach ($action->prerequisites as $prerequisite) {
            $eligible = $this->isActionPrerequisiteMet($prerequisite, $questProgressId);

            if (!$eligible) {
                // If at least one prerequisite is not met, do not continue.
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param int $actionId
     * @param int $questProgressId
     * @return QuestAction
     */
    private function addOneQuestAction(int $actionId, int $questProgressId): QuestAction {
        Yii::debug("*** debug *** addQuestAction - actionId={$actionId}, questProgressId={$questProgressId}");

        $questAction = QuestAction::findOne([
            'action_id' => $actionId,
            'quest_progress_id' => $questProgressId
        ]);

        if ($questAction) {
            Yii::debug("*** debug *** addQuestAction - Previously existing QuestAction");
            $questAction->status = null;
            $questAction->eligible = 1;
        } else {
            Yii::debug("*** debug *** addQuestAction - Create new QuestAction");
            $questAction = new QuestAction([
                'quest_progress_id' => $questProgressId,
                'action_id' => $actionId
            ]);
        }

        $this->save($questAction);
        return $questAction;
    }

    /**
     *
     * @param int $missionId
     * @return void
     */
    public function addQuestActions(int $missionId): void {
        $actions = Action::findAll(['mission_id' => $missionId]);

        foreach ($actions as $action) {
            $questProgressId = (int) $this->questProgress?->id;
            if ($this->isActionEligible($action, $questProgressId)) {
                $this->addOneQuestAction($action->id, $questProgressId);
            }
        }
    }
}
