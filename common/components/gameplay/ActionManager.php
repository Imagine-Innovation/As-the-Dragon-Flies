<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\gameplay\BaseManager;
use common\models\Action;
use common\models\ActionFlow;
use common\models\Player;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestProgress;
use Yii;

final class ActionManager extends BaseManager
{

    public ?QuestAction $questAction = null;
    public ?QuestProgress $questProgress = null;
    private ?Action $action = null;
    private ?Player $player = null;
    private ?Quest $quest = null;

    /**
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if ($this->questAction) {
            $this->questProgress = $this->questAction->questProgress;
        }

        if (!$this->questProgress) {
            throw new \Exception('Missing QuestProgress!!!');
        }

        $this->action ??= $this->questAction?->action;
        $this->player ??= $this->questProgress->currentPlayer;
        $this->quest ??= $this->questProgress->quest;
    }

    /**
     * @param Action $action
     * @param int $questProgressId
     * @return bool
     */
    public function isActionEligible(Action &$action, int $questProgressId): bool
    {
        Yii::debug("*** debug *** isActionEligible - action={$action->name}, questProgressId={$questProgressId}");
        foreach ($action->prerequisites as $prerequisite) {
            $eligible = $this->isActionPrerequisiteMet($prerequisite, $questProgressId);

            if (!$eligible) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param ActionFlow $prerequisite
     * @param int $questProgressId
     * @return bool
     */
    private function isActionPrerequisiteMet(ActionFlow &$prerequisite, int $questProgressId): bool
    {
        Yii::debug("*** debug *** isActionPrerequisiteMet - prequisite={$prerequisite->previousAction->name}, questProgressId={$questProgressId}");
        $questAction = QuestAction::findOne([
            'quest_progress_id' => $questProgressId,
            'action_id' => $prerequisite->previous_action_id,
        ]);

        if ($questAction) {
            $questActionStatus = $questAction->status;
            $criterionMask = $prerequisite->status;
            $bitwiseComparison = $questActionStatus & $criterionMask;
            Yii::debug("*** debug *** isActionPrerequisiteMet - criterionMask={$criterionMask}, questActionStatus={$questActionStatus}, bitwiseComparison={$bitwiseComparison}");

            return $bitwiseComparison === $questActionStatus;
        }

        Yii::debug('*** debug *** isActionPrerequisiteMet - questAction not found');
        return false;
    }

    /**
     * @param int $actionId
     * @param int $questProgressId
     * @return QuestAction
     */
    private function addOneQuestAction(int $actionId, int $questProgressId): QuestAction
    {
        $questAction = QuestAction::findOne([
            'action_id' => $actionId,
            'quest_progress_id' => $questProgressId,
        ]);

        if ($questAction) {
            if (!$questAction->eligible) {
                return $questAction;
            }
            // Existing and eligible action, reset the status
            $questAction->status = null;
        } else {
            $questAction = new QuestAction([
                'quest_progress_id' => $questProgressId,
                'action_id' => $actionId,
            ]);
        }

        $this->save($questAction);
        return $questAction;
    }

    /**
     * @param int $missionId
     * @return void
     */
    public function addQuestActions(int $missionId): void
    {
        $actions = Action::findAll(['mission_id' => $missionId]);

        foreach ($actions as $action) {
            $questProgressId = (int) $this->questProgress?->id;
            if ($this->isActionEligible($action, $questProgressId)) {
                $this->addOneQuestAction($action->id, $questProgressId);
            }
        }
    }

    /**
     * @param AppStatus $status
     * @param bool|null $canReplay
     * @return void
     */
    public function endCurrentAction(AppStatus $status, ?bool $canReplay = true): void
    {
        Yii::debug("*** debug *** endCurrentAction - action={$this->action?->name}, status={$status->getLabel()}");

        QuestAction::updateAll(['status' => $status->value, 'eligible' => $canReplay], [
            'action_id' => $this->questAction?->action_id,
            'quest_progress_id' => $this->questAction?->quest_progress_id,
        ]);
    }

    /**
     * @param AppStatus $status
     * @return QuestAction[]
     */
    public function unlockNextActions(AppStatus $status): array
    {
        Yii::debug("*** debug *** unlockNextActions - action={$this->action?->name}, status={$status->getLabel()}");
        $unlockedQuestActions = [];
        $triggeredActions = $this->action?->triggers;

        if ($triggeredActions === null) {
            return $unlockedQuestActions;
        }

        $questProgressId = (int) $this->questProgress?->id;
        /** @var ActionFlow $actionFlow */
        foreach ($triggeredActions as $actionFlow) {
            $bitwiseComparison = $actionFlow->status & $status->value;
            $isEligible = $this->isActionEligible($actionFlow->nextAction, $questProgressId);
            if ($bitwiseComparison && $isEligible) {
                $actionId = $actionFlow->next_action_id;
                $unlockedQuestActions[] = $this->addOneQuestAction($actionId, $questProgressId);
            }
        }

        Yii::debug('*** debug *** unlockNextActions - isEligible: ' . count($unlockedQuestActions) . ' triggered action(s)');
        return $unlockedQuestActions;
    }
}
