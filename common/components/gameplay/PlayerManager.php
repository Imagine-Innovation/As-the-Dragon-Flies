<?php

namespace common\components\gameplay;

use common\models\Item;
use common\models\Outcome;
use common\models\Player;
use common\models\Quest;
use common\models\QuestPlayer;
use common\helpers\DiceRoller;
use Yii;

class PlayerManager extends BaseManager
{

    // Context data
    // Public facade
    public ?QuestPlayer $questPlayer = null;
    public ?Quest $quest = null;
    public ?Player $player = null;
    // internal use

    /**
     *  @var array{
     *      hpLoss: int,
     *      gainedXp: int,
     *      gainedGp: int,
     *      gainedItems: non-empty-array<Item>|array{}
     *  } $stats
     */
    private array $stats;

    /**
     *
     * @param array<string, mixed> $config
     */
    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        // Align the context data from QuestAction
        if ($this->questPlayer) {
            $this->quest = $this->questPlayer->quest;
        }

        $this->player ??= $this->quest?->currentPlayer;
    }

    /**
     *
     * @return void
     */
    private function initStats(): void {
        $this->stats = [
            'hpLoss' => 0,
            'gainedXp' => 0,
            'gainedGp' => 0,
            'gainedItems' => [],
        ];
    }

    /**
     *
     * @param int $xp
     * @return int
     */
    private function getLevelId(int $xp): int {
        Yii::debug("*** debug *** getLevelId - xp={$xp}");
        $level = \common\models\Level::find()
                ->where(['<=', 'xp_min', $xp])
                ->andWhere(['>', 'xp_max', $xp])
                ->one();
        return $level->id ?? 1;
    }

    /**
     *
     * @param Player $player
     * @param int|null $gainedXp
     * @return array<string, int>
     */
    private function updateXp(Player &$player, ?int $gainedXp = 0): array {
        Yii::debug("*** debug *** updateXp - gainedXp={$gainedXp}");
        $updateSetStatement = [];

        $this->stats['gainedXp'] += $gainedXp;

        $newXP = $player->experience_points + $gainedXp;
        $updateSetStatement['experience_points'] = $newXP;

        $newLevelId = $this->getLevelId($newXP);
        if ($newLevelId <> $player->level_id) {
            $updateSetStatement['level_id'] = $newLevelId;
            // TODO : Alert the player that he has reached a new level
        }

        return $updateSetStatement;
    }

    /**
     *
     * @param Player $player
     * @param string $hpLossDice
     * @return array<string, int>
     */
    private function updateHp(Player &$player, string $hpLossDice): array {
        Yii::debug("*** debug *** updateHp - hpLossDice={$hpLossDice}");
        $hpLoss = DiceRoller::roll($hpLossDice);

        $this->stats['hpLoss'] += $hpLoss;

        // Ensure that hit points are always positive
        $newHP = max($player->hit_points - $hpLoss, 0);

        return ['hit_points' => $newHP];
    }

    /**
     *
     * @param Outcome $outcome
     * @return void
     */
    public function updatePlayerStats(Outcome &$outcome): void {
        Yii::debug("*** debug *** updatePlayerStats - player={$this->player?->name}, outcome=" . print_r($outcome, true));
        if ($this->player === null) {
            return;
        }

        $updateSetStatement = [];
        if ($outcome->gained_xp > 0) {
            $updateSetStatement = $this->updateXp($this->player, $outcome->gained_xp);
        }

        if ($outcome->hp_loss_dice) {
            $updateSetStatement = [...$updateSetStatement, ...$this->updateHp($this->player, $outcome->hp_loss_dice)];
        }

        if (!empty($updateSetStatement)) {
            Yii::debug("*** debug *** updatePlayerStats - update set=" . print_r($updateSetStatement, true));
            Player::updateAll($updateSetStatement, ['id' => $this->player->id]);
        }

        $this->stats['gainedGp'] += $outcome->gained_gp ?? 0;
        if ($outcome->item === null) {
            return;
        }
        $this->stats['gainedItems'][] = $outcome->item;
    }

    /**
     *
     * @param Outcome[] $outcomes
     * @return void
     */
    public function registerGainsAndLosses(array &$outcomes): void {
        Yii::debug("*** debug *** registerGainsAndLosses - outcomes=" . count($outcomes));

        if (empty($outcomes) || $this->player === null) {
            return;
        }
        $player = $this->player;

        $this->initStats();
        foreach ($outcomes as $outcome) {
            $this->updatePlayerStats($outcome);

            $player->addCoins($outcome->gained_gp, 'gp');
            if ($outcome->item_id) {
                $player->addItems($outcome->item_id);
            }
        }
        return;
    }
}
