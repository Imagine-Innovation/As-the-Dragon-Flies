<?php

namespace common\components\gameplay;

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
    private array $stats = [];

    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        // Align the context data from QuestAction
        if ($this->questPlayer) {
            $this->quest = $this->questPlayer->quest;
        }

        $this->player ??= $this->quest?->currentPlayer;
    }

    private function initStats(): void {
        $this->stats = [
            'hpLoss' => 0,
            'gainedXp' => 0,
            'gainedGp' => 0,
            'gainedItems' => [],
        ];
    }

    private function getLevelId(int $xp): int {
        Yii::debug("*** debug *** getLevelId - xp={$xp}");
        $level = \common\models\Level::find()
                ->where(['<=', 'xp_min', $xp])
                ->andWhere(['>', 'xp_max', $xp])
                ->one();
        return $level?->id ?? 1;
    }

    private function updateXp(?int $gainedXp = 0): array {
        Yii::debug("*** debug *** updateXp - gainedXp={$gainedXp}");
        $updateSetStatement = [];

        $this->stats['gainedXp'] += $gainedXp;

        $newXP = $this->player->experience_points + $gainedXp;
        $updateSetStatement['experience_points'] = $newXP;

        $newLevelId = $this->getLevelId($newXP);
        if ($newLevelId <> $this->player->level_id) {
            $updateSetStatement['level_id'] = $newLevelId;
            // TODO : Alert the player that he has reached a new level
        }

        return $updateSetStatement;
    }

    private function updateHp(string $hpLossDice): array {
        Yii::debug("*** debug *** updateHp - hpLossDice={$hpLossDice}");
        $hpLoss = DiceRoller::roll($hpLossDice);

        $this->stats['hpLoss'] += $hpLoss;

        // Ensure that hit points are always positive
        $newHP = max($this->player->hit_points - $hpLoss, 0);

        return ['hit_points' => $newHP];
    }

    public function updatePlayerStats(Outcome $outcome): void {
        Yii::debug("*** debug *** updatePlayerStats - player={$this?->player?->name}, outcome=" . print_r($outcome, true));
        If (!$this->player) {
            return;
        }

        $updateSetStatement = [];
        if ($outcome->gained_xp > 0) {
            $updateSetStatement = $this->updateXp($outcome->gained_xp);
        }

        if ($outcome->hp_loss_dice) {
            $updateSetStatement = [...$updateSetStatement, ...$this->updateHp($outcome->hp_loss_dice)];
        }

        if (!empty($updateSetStatement)) {
            Yii::debug("*** debug *** updatePlayerStats - update set=" . print_r($updateSetStatement, true));
            Player::updateAll($updateSetStatement, ['id' => $this->player->id]);
        }

        $this->stats['gainedGp'] += $outcome->gained_gp ?? 0;
        if ($outcome->item_id) {
            $this->stats['gainedItems'][] = $outcome->item->name;
        }
    }

    public function registerGainsAndLosses(array $outcomes): array {
        Yii::debug("*** debug *** registerGainsAndLosses - outcomes=" . count($outcomes));

        if (empty($outcomes)) {
            // nothing to register
            return [];
        }

        $this->initStats();
        foreach ($outcomes as $outcome) {
            $this->updatePlayerStats($outcome);

            $this->player->addCoins($outcome->gained_gp, 'gp');
            $this->player->addItems($outcome->item_id);
        }
        return $this->stats;
    }
}
