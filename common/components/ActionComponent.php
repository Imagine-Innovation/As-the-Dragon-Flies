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
    private ?Action $action = null;
    public ?QuestAction $questAction = null;
    public ?QuestProgress $questProgress = null;
    private ?Player $player = null;
    private ?int $nextMissionId = null;
    private int $hpLoss = 0;

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
        $this->player ??= $this->questProgress?->currentPlayer;
    }

    protected function getModifier(): int {
        Yii::debug("*** debug *** getModifier - action={$this->action->name}, player={$this->player->name}");
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

    protected function determineActionStatus(int $diceRoll): AppStatus {
        Yii::debug("*** debug *** determineActionStatus - action={$this->action->name}, diceRoll={$diceRoll}");
        $dc = $this->action->dc;
        $partialDc = $this->action->partial_dc;

        if ($diceRoll >= $dc) {
            return AppStatus::SUCCESS;
        }

        if ($partialDc !== null && $diceRoll >= $partialDc) {
            return AppStatus::PARTIAL;
        }

        return AppStatus::FAILURE;
    }

    protected function endCurrentAction(AppStatus $status, ?bool $canReplay = true): void {
        Yii::debug("*** debug *** endCurrentAction - action={$this->action->name}, status={$status->getLabel()}");

        QuestAction::updateAll(
                ['status' => $status->value, 'eligible' => $canReplay],
                ['action_id' => $this->questAction->action_id, 'quest_progress_id' => $this->questAction->quest_progress_id]
        );
    }

    protected function unlockNextActions(AppStatus $status): array {
        Yii::debug("*** debug *** unlockNextActions - action={$this->action->name}, status={$status->getLabel()}");
        $triggeredActions = $this->action->triggers;

        $unlockedQuestActions = [];
        $questProgressId = $this->questProgress->id;
        foreach ($triggeredActions as $actionFlow) {
            // Bitwise comparison between actual status and expected status to unlock next action
            $bitwiseComparison = ($actionFlow->status & $status->value);
            $isEligible = $this->isActionEligible($actionFlow->nextAction, $questProgressId);
            Yii::debug("*** debug *** unlockNextActions - isEligible=" . ($isEligible ? 'true' : 'false') . ", bitwis comparison={$bitwiseComparison}");
            if ($bitwiseComparison && $isEligible) {
                $actionId = $actionFlow->next_action_id;
                $unlockedQuestActions[] = $this->addQuestAction($actionId, $questProgressId);
            }
        }
        Yii::debug("*** debug *** unlockNextActions - isEligible: " . count($unlockedQuestActions) . " triggered action(s)");
        return $unlockedQuestActions;
    }

    protected function getLevelId(int $xp): int {
        Yii::debug("*** debug *** getLevelId - xp={$xp}");
        $level = \common\models\Level::find()
                ->where(['<=', 'xp_min', $xp])
                ->andWhere(['>', 'xp_max', $xp])
                ->one();
        return $level?->id ?? 1;
    }

    protected function updateXp(int $gainedXp): array {
        Yii::debug("*** debug *** updateXp - gainedXp=" . ($gainedXp ?? 'null'));
        $setUpdate = [];

        $newXP = $this->player->experience_points + $gainedXp;
        $setUpdate['experience_points'] = $newXP;

        $newLevelId = $this->getLevelId($newXP);
        if ($newLevelId <> $this->player->level_id) {
            $setUpdate['level_id'] = $newLevelId;
            // TODO : Alert the player that he has reached a new level
        }

        return $setUpdate;
    }

    protected function updateHp(string $hpLossDice): array {
        Yii::debug("*** debug *** updateHp - hpLossDice=" . ($hpLossDice ?? 'null'));
        $hpLoss = DiceRoller::roll($hpLossDice);

        $this->hpLoss += $hpLoss;

        // Ensure that hit points are always positive
        $newHP = max($this->player->hit_points - $hpLoss, 0);

        return ['hit_points' => $newHP];
    }

    protected function updatePlayerStats(?int $gainedXp, ?string $hpLossDice) {
        Yii::debug("*** debug *** updatePlayerStats - player={$this?->player?->name}, gainedXp=" . ($gainedXp ?? 'null') . ", hpLossDice=" . ($hpLossDice ?? 'null'));
        If (!$this->player) {
            return;
        }

        $setUpdate = [];
        if ($gainedXp > 0) {
            $setUpdate = $this->updateXp($gainedXp);
        }

        if ($hpLossDice) {
            $setUpdate = [...$setUpdate, ...$this->updateHp($hpLossDice)];
        }

        if (!empty($setUpdate)) {
            Yii::debug("*** debug *** updatePlayerStats - update set=" . print_r($setUpdate, true));
            Player::updateAll($setUpdate, ['id' => $this->player->id]);
        }
    }

    protected function getOutcomes(AppStatus $status): array {
        Yii::debug("*** debug *** getOutcomes - status={$status->getLabel()}");
        $outcomes = Outcome::findAll(['action_id' => $this->action->id]);

        if (!$outcomes) {
            // nothing to register
            return [];
        }

        $selectedOutcomes = [];
        foreach ($outcomes as $outcome) {
            if ($outcome->status & $status->value) {
                $selectedOutcomes[] = $outcome;
            }
        }
        return $selectedOutcomes;
    }

    protected function registerGainsAndLosses(array $outcomes): ?bool {
        Yii::debug("*** debug *** registerGainsAndLosses - outcomes=" . count($outcomes));

        if (empty($outcomes)) {
            // nothing to register
            return null;
        }

        $nextMissionId = null;
        $canReplay = true;
        foreach ($outcomes as $outcome) {
            $canReplay = $canReplay && ($outcome->can_replay === 1);
            $nextMissionId = ($outcome->next_mission_id === $this->questProgress->mission_id) ? null : $outcome->next_mission_id;
            $this->updatePlayerStats($outcome->gained_xp, $outcome->hp_loss_dice);

            $this->player->addCoins($outcome->gained_gp, 'gp');
            $this->player->addItems($outcome->item_id);
        }
        $this->nextMissionId = $nextMissionId;
        return $canReplay;
    }

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
        $canReplay = $this->registerGainsAndLosses($outcomes);
        $this->endCurrentAction($status, $canReplay);
        $this->unlockNextActions($status);

        return [
            'action' => $this->action,
            'status' => $status,
            'outcomes' => $outcomes,
            'diceRoll' => "Rolling {$diceToRoll} gave {$diceRoll}",
            'hpLoss' => $this->hpLoss,
            'isFree' => $this->action->is_free,
            'canReplay' => $canReplay,
            'questProgressId' => $this->questProgress->id,
            'nextMissionId' => $this->nextMissionId,
        ];
    }

    protected function isActionPrerequisiteMet(ActionFlow &$prerequisite, int $questProgressId): bool {
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

    public function isActionEligible(Action &$action, int $questProgressId): bool {
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

    public function addQuestAction(int $actionId, int $questProgressId): QuestAction {
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

    protected function save(\yii\db\ActiveRecord $model) {
        if (!$model->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($model->errors, 0, false)));
        }
    }
}
