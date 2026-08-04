<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\gameplay\BaseManager;
use common\helpers\DiceRoller;
use common\helpers\LanguageHelper;
use common\helpers\MergeHelper;
use common\models\Action;
use common\models\ActionTypeSkill;
use common\models\Outcome;
use common\models\Player;
use common\models\PlayerItem;
use common\models\PlayerSkill;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestLog;
use common\models\QuestProgress;
use Yii;

final class OutcomeManager extends BaseManager
{

    // Public facade
    public QuestAction $questAction;
    //
    // Internal use
    private QuestProgress $questProgress;
    private Action $action;
    private Player $player;
    private Quest $quest;
    private ?int $nextMissionId = null;
    private int $hpLoss = 0;
    private string $language = 'en';

    /**
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->questProgress = $this->questAction->questProgress;

        $this->action = $this->questAction->action;
        $this->player = $this->questProgress->currentPlayer;
        $this->quest = $this->questProgress->quest;

        $this->language = $this->quest->story->language ?? 'en';
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
        $outcomes = Outcome::findAll(['action_id' => $this->action->id]);

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
    private function playerCanReplay(array $outcomes): bool
    {
        Yii::debug('*** debug *** canReplay - outcomes=' . count($outcomes));

        if (empty($outcomes)) {
            return false;
        }

        $nextMissionId = null;
        $canReplay = true;
        $currentMissionId = (int) $this->questProgress->mission_id;

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
    private function setPlayerGainsAndLosses(array $outcomes): void
    {
        $playerManager = new PlayerManager(['player' => $this->player]);
        $playerManager->registerGainsAndLosses($outcomes);
        $this->hpLoss = $playerManager->stats['hpLoss'];
    }

    /**
     * Builds and structures the final evaluation outcome payload.
     *
     * @param AppStatus $status
     * @param Outcome[] $outcomes
     * @param int $diceRoll
     * @param bool $canReplay
     * @return array<string, mixed>
     */
    private function setPayload(AppStatus $status, array $outcomes, int $diceRoll, bool $canReplay): array
    {
        $missionId = $this->questProgress->mission_id;
        $playerName = LanguageHelper::defaultName('Player', $this->player->name, $this->language);

        return [
            'action' => $this->action,
            'status' => $status,
            'outcomes' => $outcomes,
            'diceRoll' => $diceRoll,
            'hpLoss' => $this->hpLoss,
            'isFree' => $this->action->is_free,
            'canReplay' => $canReplay,
            'questProgressId' => $this->questProgress->id,
            'missionId' => $missionId,
            'nextMissionId' => $this->nextMissionId,
            'storyId' => $this->quest->story_id,
            'playerName' => $playerName,
            'language' => $this->language,
        ];
    }

    /**
     * @return int
     */
    private function getModifier(): int
    {
        Yii::debug("*** debug *** getModifier - action={$this->action->name}, player={$this->player->name}");
        $skillIds = ActionTypeSkill::find()
                ->select('skill_id')
                ->where(['action_type_id' => $this->action->action_type_id])
                ->column();

        if (!$skillIds) {
            return 0;
        }

        $modifier = PlayerSkill::find()
                ->where(['player_id' => $this->player->id, 'skill_id' => $skillIds])
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
        Yii::debug("*** debug *** getActionStatus - action={$this->action->name}, diceRoll={$diceRoll}");
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

    /**
     * Evaluates action, triggers outcome processing, and updates state flow.
     *
     * @return array{payload: array<string, mixed>, log: array<string, mixed>}
     * @throws \Exception
     */
    public function evaluateActionOutcome(): array
    {
        Yii::debug('*** debug *** evaluateActionOutcome');

        $modifier = $this->getModifier();
        $diceToRoll = $modifier ? "1d20+{$modifier}" : 'd20';
        $diceRoll = DiceRoller::roll($diceToRoll);

        if ($this->playerHasRequiredItem($this->player->id, $this->action->required_item_id)) {
            $status = $this->getActionStatus($diceRoll);
        } else {
            $status = AppStatus::ITEM_MISSING;
        }

        $outcomes = $this->getMatchingOutcomes($status);

        $canReplay = $this->playerCanReplay($outcomes);

        $this->setPlayerGainsAndLosses($outcomes);

        $payload = $this->setPayload($status, $outcomes, $diceRoll, $canReplay);
        $log = $this->setQuestLog($status, $outcomes, $diceToRoll, $diceRoll);

        return ['payload' => $payload, 'log' => $log];
    }

    /**
     *
     * @param string $diceToRoll
     * @param int $diceRoll
     * @return string
     */
    private function getDiceRoll(string $diceToRoll, int $diceRoll): string
    {
        return Yii::t('app/game', 'rolling dice result',
                        [
                            'diceToRoll' => $diceToRoll,
                            'diceRoll' => $diceRoll,
                        ],
                        $this->language
                );
    }

    /**
     *
     * @param string $playerName
     * @param AppStatus $status
     * @return string
     */
    private function getActionResult(string $playerName, AppStatus $status): string
    {
        Yii::debug("*** debug *** getActionResult(playerName={$playerName}, status={$status->name})");
        return Yii::t('app/game', 'action status',
                        [
                            'playerName' => $playerName,
                            'actionName' => $this->action->name,
                            'status' => $status->name,
                        ],
                        $this->language
                );
    }

    /**
     *
     * @param int $questId
     * @return int
     */
    private function getNextRound(int $questId): int
    {
        $maxRoundVal = QuestLog::find()
                ->where(['quest_id' => $questId])
                ->max('round');

        $maxRound = is_numeric($maxRoundVal) ? (int) $maxRoundVal : 0;

        return $maxRound + 1;
    }

    /**
     *
     * @param list<array{name: string|null, image: string|null, description: string|null, actionOutcome: array<string>}> $outcomeList
     * @return string
     */
    private function getLogDescription(array $outcomeList): string
    {
        $description = '';
        /** @var array{name: string|null, image: string|null, description: string|null, actionOutcome: array<string>} $outcome */
        foreach ($outcomeList as $outcome) {
            $description .= "{$outcome['name']}\n{$outcome['description']}\n";
        }
        return $description;
    }

    /**
     *
     * @param array<string, mixed> $log
     * @return void
     */
    public function addQuestLog(array $log): void
    {
        $round = $this->getNextRound($this->quest->id);
        /** @var list<array{name: string|null, image: string|null, description: string|null, actionOutcome: array<string>}> $outcomeList */
        $outcomeList = $log['outcomeList'];
        $description = $this->getLogDescription($outcomeList);

        $questLog = new QuestLog([
            'quest_id' => $this->quest->id,
            'player_id' => $this->player->id,
            'round' => $round,
            'chapter_name' => $log['chapterName'],
            'mission_name' => $log['missionName'],
            'action_name' => $log['actionName'],
            'dice_roll' => $log['diceRoll'],
            'action_success' => $log['actionStatus'],
            'description' => $description,
        ]);

        if (!$questLog->save()) {
            Yii::error('Failed to save QuestLog: ' . print_r($questLog->getErrors(), true));
        }
    }

    /**
     *
     * @param AppStatus $status
     * @param Outcome[] $outcomes
     * @param string $diceToRoll
     * @param int $diceRoll
     * @return array<string, mixed>
     */
    private function setQuestLog(AppStatus $status, array $outcomes, string $diceToRoll, int $diceRoll): array
    {
        $playerName = LanguageHelper::defaultName('Player', $this->player->name, $this->language);

        $outcomeList = $this->getOutcomeList($outcomes, $playerName);

        $log = [
            'playerName' => $playerName,
            'chapterName' => LanguageHelper::defaultName('Chapter', $this->quest->currentChapter?->name, $this->language),
            'missionName' => $this->questProgress->mission->name,
            'actionName' => MergeHelper::merge($this->action->name, ['playerName' => $playerName]),
            'actionDescription' => MergeHelper::merge($this->action->description, ['playerName' => $playerName]),
            'actionStatus' => $this->getActionResult($playerName, $status),
            'diceRoll' => $this->getDiceRoll($diceToRoll, $diceRoll),
            'hpLoss' => Yii::t('app/game', 'loosing hp', ['hpLoss' => $this->hpLoss], $this->language),
            'outcomeList' => $outcomeList,
            'isFree' => $this->action->is_free,
            'questProgressId' => $this->questProgress->id,
            'nextMissionId' => $this->nextMissionId,
            'storyId' => $this->quest->story_id,
        ];

        return $log;
    }

    /**
     *
     * @param Outcome[] $outcomes
     * @param string $playerName
     * @return list<array{name: string|null, image: string|null, description: string|null, actionOutcome: array<string>}>
     */
    private function getOutcomeList(array $outcomes, string $playerName): array
    {
        if (empty($outcomes)) {
            return [
                [
                    'name' => '',
                    'image' => null,
                    'description' => Yii::t('app/game', 'Something happened', $this->language),
                    'actionOutcome' => [],
                ]
            ];
        }

        $outcomeLog = [];
        foreach ($outcomes as $outcome) {
            $actionOutcome = $this->getActionOutcome($outcome);
            $outcomeLog[] = [
                'name' => MergeHelper::merge($outcome->name, ['playerName' => $playerName]),
                'image' => $outcome->image,
                'description' => MergeHelper::merge($outcome->description, ['playerName' => $playerName]),
                'actionOutcome' => $actionOutcome,
            ];
        }

        return $outcomeLog;
    }

    /**
     *
     * @param Outcome $outcome
     * @return array<string>
     */
    private function getActionOutcome(Outcome $outcome): array
    {
        $actionOutcome = [];
        if ($outcome->gained_gp > 0) {
            $actionOutcome[] = Yii::t('app/game', 'gained gp', ['gp' => $outcome->gained_gp], $this->language);
        }

        if ($outcome->gained_xp > 0) {
            $actionOutcome[] = Yii::t('app/game', 'gained xp', ['xp' => $outcome->gained_xp], $this->language);
        }

        if ($outcome->item_id) {
            $itemName = LanguageHelper::defaultName('Item', $outcome->item?->name, $this->language);
            $actionOutcome[] = Yii::t('app/game', 'gained item', ['itemName' => strtolower($itemName)], $this->language);
        }
        return $actionOutcome;
    }
}
