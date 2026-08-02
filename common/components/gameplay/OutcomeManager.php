<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\gameplay\BaseManager;
use common\helpers\DiceRoller;
use common\models\Action;
use common\models\ActionTypeSkill;
use common\models\Outcome;
use common\models\Player;
use common\models\PlayerItem;
use common\models\PlayerSkill;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestProgress;
use Yii;

final class OutcomeManager extends BaseManager
{

    public ?QuestAction $questAction = null;
    public ?QuestProgress $questProgress = null;
    public ?Action $action = null;
    public ?Player $player = null;
    public ?Quest $quest = null;
    private ?int $nextMissionId = null;
    private int $hpLoss = 0;

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
     * Retrieves active outcomes matching the given status mask.
     *
     * @param AppStatus $status
     * @return Outcome[]
     */
    private function getMatchingOutcomes(AppStatus $status): array
    {
        Yii::debug("*** debug *** getMatchingOutcomes - status={$status->getLabel()}");
        $outcomes = Outcome::findAll(['action_id' => $this->action?->id]);

        if (!$outcomes) {
            return [];
        }

        $selectedOutcomes = [];
        foreach ($outcomes as $outcome) {
            $bitwiseComparison = $outcome->status & $status->value;

            if ($bitwiseComparison) {
                $selectedOutcomes[] = $outcome;
            }
        }

        return $selectedOutcomes;
    }

    /**
     * Determines whether the action can be replayed and tracks potential mission updates.
     *
     * @param Outcome[] $outcomes
     * @return bool
     */
    private function canReplay(array $outcomes): bool
    {
        Yii::debug('*** debug *** canReplay - outcomes=' . count($outcomes));

        if (empty($outcomes)) {
            return false;
        }

        $nextMissionId = null;
        $canReplay = true;
        $currentMissionId = (int) $this->questProgress?->mission_id;

        foreach ($outcomes as $outcome) {
            $canReplay = $canReplay && $outcome->can_replay === 1;
            if ($nextMissionId === null && $outcome->next_mission_id !== null && (int) $outcome->next_mission_id !== $currentMissionId) {
                $nextMissionId = (int) $outcome->next_mission_id;
            }
        }
        $this->nextMissionId = $nextMissionId;
        return $canReplay;
    }

    /**
     * Triggers player gains and losses via PlayerManager.
     *
     * @param Outcome[] $outcomes
     * @return void
     */
    private function applyPlayerGainsAndLosses(array $outcomes): void
    {
        $this->hpLoss = 0; // Reset local counter
        if ($this->player) {
            $playerManager = new PlayerManager(['player' => $this->player]);
            $playerManager->registerGainsAndLosses($outcomes);
        }
    }

    /**
     * Builds and structures the final evaluation outcome payload.
     *
     * @param Action $action
     * @param Quest|null $quest
     * @param AppStatus $status
     * @param Outcome[] $outcomes
     * @param string $diceToRoll
     * @param int $diceRoll
     * @param bool $canReplay
     * @return array<string, mixed>
     */
    private function buildEvaluationSummary(Action $action, ?Quest $quest, AppStatus $status, array $outcomes, string $diceToRoll, int $diceRoll, bool $canReplay): array
    {
        $missionId = $this->questProgress?->mission_id;

        $diceRollLabel = Yii::t('app/game', 'rolling dice result', [
            'diceToRoll' => $diceToRoll,
            'diceRoll' => $diceRoll,
                ], $quest->story->language ?? 'en');

        return [
            'action' => $action,
            'status' => $status,
            'outcomes' => $outcomes,
            'diceRoll' => $diceRoll,
            'diceRollLabel' => $diceRollLabel,
            'hpLoss' => $this->hpLoss,
            'isFree' => $action->is_free,
            'canReplay' => $canReplay,
            'questProgressId' => $this->questProgress?->id,
            'missionId' => $missionId,
            'nextMissionId' => $this->nextMissionId,
            'storyId' => $quest?->story_id,
        ];
    }

    /**
     * @return int
     */
    private function getModifier(): int
    {
        Yii::debug("*** debug *** getModifier - action={$this->action?->name}, player={$this->player?->name}");
        $skillIds = ActionTypeSkill::find()
                ->select('skill_id')
                ->where(['action_type_id' => $this->action?->action_type_id])
                ->column();

        if (!$skillIds) {
            return 0;
        }

        $modifier = PlayerSkill::find()
                ->where(['player_id' => $this->player?->id, 'skill_id' => $skillIds])
                ->max('bonus');

        return is_numeric($modifier) ? (int) $modifier : 0;
    }

    /**
     * @param int|null $playerId
     * @param int|null $itemId
     * @return bool
     */
    private function playerHasRequiredItem(?int $playerId, ?int $itemId): bool
    {
        if ($itemId === null) {
            return true;
        }

        $playerItem = PlayerItem::findOne(['player_id' => $playerId, 'item_id' => $itemId]);

        return $playerItem !== null && $playerItem->quantity > 0;
    }

    /**
     * @param int $diceRoll
     * @return AppStatus
     */
    private function getActionStatus(int $diceRoll): AppStatus
    {
        Yii::debug("*** debug *** getActionStatus - action={$this->action?->name}, diceRoll={$diceRoll}");
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
     * Evaluates action, triggers outcome processing, and updates state flow.
     *
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function evaluateActionOutcome(): array
    {
        Yii::debug('*** debug *** evaluateActionOutcome');
        if (!$this->action) {
            throw new \Exception('Action not found.');
        }

        $modifier = $this->getModifier();
        $diceToRoll = $modifier ? "1d20+{$modifier}" : 'd20';
        $diceRoll = DiceRoller::roll($diceToRoll);

        if ($this->playerHasRequiredItem($this->player?->id, $this->action->required_item_id)) {
            $status = $this->getActionStatus($diceRoll);
        } else {
            $status = AppStatus::ITEM_MISSING;
        }

        $outcomes = $this->getMatchingOutcomes($status);

        $canReplay = $this->canReplay($outcomes);

        $this->applyPlayerGainsAndLosses($outcomes);

        return $this->buildEvaluationSummary($this->action, $this->quest, $status, $outcomes, $diceToRoll, $diceRoll, $canReplay);
    }
}
