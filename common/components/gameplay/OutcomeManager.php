<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\gameplay\BaseManager;
use common\components\NarrativeComponent;
use common\helpers\DiceRoller;
use common\helpers\LanguageHelper;
use common\helpers\MergeHelper;
use common\models\Action;
use common\models\ActionTypeSkill;
use common\models\Chapter;
use common\models\ChapterLog;
use common\models\Outcome;
use common\models\Mission;
use common\models\MissionLog;
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
    public ?string $dialogLog = null;
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
        Yii::debug("*** debug *** getMatchingOutcomes - status={$status->name}");
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
        $currentMissionId = $this->questProgress->mission_id;

        foreach ($outcomes as $outcome) {
            // The player may take another turn if all the outcomes indicate that they may do so
            $canReplay = $canReplay && ($outcome->can_replay === 1);

            // If multiple outcomes lead to a next mission, the first mission found will be selected.
            if ($nextMissionId === null && $outcome->next_mission_id !== null && $outcome->next_mission_id !== $currentMissionId) {
                $nextMissionId = $outcome->next_mission_id;
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
        $stats = $playerManager->stats;
        $this->hpLoss = $stats['hpLoss'];
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
        Yii::debug("*** debug *** OutcomeManager - setPayload(status={$status->name}, diceRoll={$diceRoll}, canReplay={$canReplay})");
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
        $requiredSkillsIds = ActionTypeSkill::find()
                ->select('skill_id')
                ->where(['action_type_id' => $this->action->action_type_id])
                ->column();

        if (!$requiredSkillsIds) {
            return 0;
        }

        $playerSkills = $this->getPlayerSkills($this->player->id, $requiredSkillsIds);

        if ($playerSkills === []) {
            return 0;
        }
        Yii::debug("*** debug *** getModifier -> bonuses=" . print_r($playerSkills, true));

        $modifier = $this->skillCheckModifier($playerSkills);

        Yii::debug("*** debug *** getModifier -> modifier={$modifier}");
        return $modifier;
    }

    /**
     * Skill Check Modifier
     *
     * For each required skill, calculate its modifier by adding the associated ability modifier and,
     * if the player is proficient in that skill, its proficiency bonus.
     *
     * The highest calculated modifier is used as the base modifier.
     *
     * If the player is proficient in additional required skills, add the proficiency bonus
     * from each additional proficient skill to the base modifier.
     *
     * Final Modifier = Highest Skill Modifier + Additional Proficiency Bonuses
     *
     * For example, if an action requires Arcana and Investigation, and the character has
     * Intelligence +3 and a +3 proficiency bonus:
     * - Proficient in neither: +3
     * - Proficient in Arcana only: +6
     * - Proficient in Investigation only: +6
     * - Proficient in both: +9
     *
     * @param array<array{skillName: string, isProficient: bool, skillBonus: int, abilityModifier: int}> $playerSkills
     * @return int
     */
    private function skillCheckModifier(array $playerSkills): int
    {
        $bestModifier = 0;
        $additionalBonus = 0;
        $hasProficiency = false;

        foreach ($playerSkills as $skill) {
            /** @var array{skillName: string, isProficient: bool, skillBonus: int, abilityModifier: int} $playerSkills $skill */
            Yii::debug($skill);
            $modifier = $skill['abilityModifier'];

            if ($skill['isProficient']) {
                $modifier += $skill['skillBonus'];
                $additionalBonus += $hasProficiency ? $skill['skillBonus'] : 0;
                $hasProficiency = true;
            }

            $bestModifier = max($bestModifier, $modifier);
        }

        return $bestModifier + $additionalBonus;
    }

    /**
     *
     * @param int $playerId
     * @param array<int> $requiredSkillsIds
     * @return array<array{skillName: string, isProficient: bool, skillBonus: int, abilityModifier: int}>
     */
    private function getPlayerSkills(int $playerId, array $requiredSkillsIds): array
    {
        $playerSkills = PlayerSkill::find()
                        ->alias('ps')
                        ->select([
                            'skillName' => 's.name',
                            'isProficient' => 'ps.is_proficient',
                            'skillBonus' => 'ps.bonus',
                            'abilityModifier' => 'pa.modifier',
                        ])
                        ->innerJoin(
                                ['s' => \common\models\Skill::tableName()],
                                's.id = ps.skill_id'
                        )
                        ->innerJoin(
                                ['pa' => \common\models\PlayerAbility::tableName()],
                                'pa.player_id = ps.player_id AND pa.ability_id = s.ability_id'
                        )
                        ->where(['ps.player_id' => $playerId, 'ps.skill_id' => $requiredSkillsIds])
                        ->asArray()->all();

        /** @var array<array{skillName: string, isProficient: bool, skillBonus: int, abilityModifier: int}> $playerSkills */
        return $playerSkills;
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
     */
    public function evaluateActionResult(): array
    {
        Yii::debug('*** debug *** evaluateActionResult');

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
        return Yii::t('app/game', 'action result',
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
     * @param AppStatus $status
     * @return string
     */
    private function getSimpleActionResult(AppStatus $status): string
    {
        Yii::debug("*** debug *** getSimpleActionResult(status={$status->name})");
        return Yii::t('app/game', 'simple action result',
                        ['status' => $status->name],
                        $this->language
                );
    }

    /**
     *
     * @param int $questId
     * @return int
     */
    private function getQuestRound(int $questId): int
    {
        $lastRound = QuestLog::find()
                ->where(['quest_id' => $questId])
                ->max('round');

        $lastQuestRound = is_numeric($lastRound) ? (int) $lastRound : 0;

        return $lastQuestRound + 1;
    }

    /**
     *
     * @param array<string, mixed> $log
     * @return void
     */
    public function logQuestAction(array $log): void
    {
        $round = $this->getQuestRound($this->quest->id);

        $dc = $this->action->dc;

        $questLog = new QuestLog([
            'quest_id' => $this->quest->id,
            'player_id' => $this->player->id,
            'chapter_id' => $log['chapterId'],
            'mission_id' => $log['missionId'],
            'round' => $round,
            'action_name' => $log['actionName'],
            'action_description' => $log['actionDescription'],
            'dice_roll' => $dc > 0 ? $log['diceRoll'] : null,
            'result' => $dc > 0 ? $log['result'] : null,
            'description' => json_encode($log['outcomeList']),
        ]);

        $this->save($questLog);
    }

    /**
     *
     * @param int $questId
     * @param Chapter|null $chapter
     * @return void
     */
    private function logChapter(int $questId, ?Chapter $chapter): void
    {
        $existingChapterLog = ChapterLog::findOne(['chapter_id' => $chapter->id ?? 0, 'quest_id' => $questId]);

        if ($existingChapterLog !== null) {
            return;
        }

        $chapterLog = new ChapterLog([
            'chapter_id' => $chapter->id ?? 0,
            'quest_id' => $questId,
            'name' => $chapter->name ?? 'Unknown chapter',
            'image' => $chapter->image ?? null,
            'description' => $chapter->description ?? '',
        ]);

        $chapterLog->save(false);
    }

    /**
     *
     * @param int $questId
     * @param Mission|null $mission
     * @return void
     */
    private function logMission(int $questId, ?Mission $mission): void
    {
        $existingMissionLog = MissionLog::findOne(['mission_id' => $mission->id ?? 0, 'quest_id' => $questId]);

        if ($existingMissionLog !== null) {
            return;
        }
        $narrative = new NarrativeComponent([
            'mission' => $mission,
            'title' => false,
            'sections' => ['decors'],
        ]);

        $missionLog = new MissionLog([
            'mission_id' => $mission->id ?? 0,
            'quest_id' => $questId,
            'name' => $mission->name ?? 'Unknown mission',
            'image' => $mission->image ?? null,
            'description' => $narrative->rawDescription(),
        ]);

        $missionLog->save(false);
    }

    /**
     *
     * @return int
     */
    private function getQuestProgressId(): int
    {
        if ($this->nextMissionId === null) {
            Yii::debug("*** debug *** OutcomeManager - getQuestProgressId(): no next mission Id");
            return $this->questProgress->id;
        }

        // There is a next mission defined.
        // We check to make sure it hasn't already been processed.
        $nextQuestProgress = QuestProgress::find()
                ->where([
                    'quest_id' => $this->quest->id,
                    'mission_id' => $this->nextMissionId
                ])
                ->one();

        if ($nextQuestProgress) {
            // If we find a record of the next mission, we reuse its ID
            Yii::debug("*** debug *** OutcomeManager - getQuestProgressId(): return existing questProgress id {$nextQuestProgress->id}");
            return $nextQuestProgress->id;
        }
        Yii::debug("*** debug *** OutcomeManager - getQuestProgressId(): return current questProgress id {$this->questProgress->id}");
        return $this->questProgress->id;
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

        $actionDescription = MergeHelper::merge($this->action->description, ['playerName' => $playerName]);
        if ($this->dialogLog !== null && trim($this->dialogLog) !== '') {
            $actionDescription = $this->dialogLog;
        }

        $chapter = $this->quest->currentChapter;
        $mission = $this->questProgress->mission;

        $this->logChapter($this->quest->id, $chapter);
        $this->logMission($this->quest->id, $mission);

        $log = [
            'playerName' => $playerName,
            'chapterId' => $chapter->id ?? 0,
            'missionId' => $mission->id,
            'actionName' => MergeHelper::merge($this->action->name, ['playerName' => $playerName]),
            'actionDescription' => $actionDescription,
            'shortResult' => $this->getSimpleActionResult($status),
            'result' => $this->getActionResult($playerName, $status),
            'diceRoll' => $this->getDiceRoll($diceToRoll, $diceRoll),
            'hpLoss' => Yii::t('app/game', 'loosing hp', ['hpLoss' => $this->hpLoss], $this->language),
            'outcomeList' => $outcomeList,
            'isFree' => $this->action->is_free,
            'questProgressId' => $this->getQuestProgressId(),
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
            $outcomeLog[] = [
                'name' => MergeHelper::merge($outcome->name, ['playerName' => $playerName]),
                'image' => $outcome->image,
                'description' => MergeHelper::merge($outcome->description, ['playerName' => $playerName]),
                'actionOutcome' => $this->getActionOutcome($outcome),
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
