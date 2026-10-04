<?php

namespace common\components\gameplay;

use common\helpers\DiceRoller;
use common\models\Item;
use common\models\Outcome;
use common\models\Player;
use common\models\PlayerAbility;
use common\models\PlayerBody;
use common\models\Quest;
use common\models\QuestPlayer;
use Yii;

class PlayerManager extends BaseManager
{

    // Context data
    // Public facade
    public ?QuestPlayer $questPlayer = null;
    public ?Quest $quest = null;
    public ?Player $player = null;

    /**
     *  @var array{
     *      hpLoss: int,
     *      gainedXp: int,
     *      gainedGp: int,
     *      gainedItems: non-empty-array<Item>|array{}
     *  } $stats
     */
    public array $stats = [
        'hpLoss' => 0,
        'gainedXp' => 0,
        'gainedGp' => 0,
        'gainedItems' => [],
    ];

    /**
     *
     * @param array<string, mixed> $config
     */
    public function __construct($config = [])
    {
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
    private function initStats(): void
    {
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
    private function getLevelId(int $xp): int
    {
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
    private function updateXp(Player &$player, ?int $gainedXp = 0): array
    {
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
    private function updateHp(Player &$player, string $hpLossDice): array
    {
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
    public function updatePlayerStats(Outcome &$outcome): void
    {
        Yii::debug(
                "*** debug *** updatePlayerStats - player={$this->player?->name}, outcome=" . print_r($outcome->attributes, true),
        );
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
            Yii::debug('*** debug *** updatePlayerStats - update set=' . print_r($updateSetStatement, true));
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
    public function registerGainsAndLosses(array &$outcomes): void
    {
        Yii::debug('*** debug *** registerGainsAndLosses - outcomes=' . count($outcomes));

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

    /**
     * Retrieves the player's Dexterity modifier.
     *
     * @param Player $player
     * @return int
     */
    private function getDexterityModifier(Player $player): int
    {
        $dexAbility = PlayerAbility::find()
            ->alias('pa')
            ->innerJoin(['a' => 'ability'], 'a.id = pa.ability_id')
            ->where(['pa.player_id' => $player->id, 'a.code' => 'DEX'])
            ->one();

        return $dexAbility ? (int) $dexAbility->modifier : 0;
    }

    /**
     * Calculates base Armor Class contributed by chest armor or unarmored defense.
     *
     * @param PlayerBody $playerBody
     * @param int $dexModifier
     * @return int
     */
    private function getChestBaseArmorClass(PlayerBody $playerBody, int $dexModifier): int
    {
        if (!$playerBody->chest_item_id) {
            return 10 + $dexModifier;
        }

        $chestItem = Item::findOne($playerBody->chest_item_id);
        if (!$chestItem || !$chestItem->armor || $chestItem->armor->armor_class <= 0) {
            return 10 + $dexModifier;
        }

        $armor = $chestItem->armor;
        $chestAc = $armor->armor_class;
        $chestDexMod = $dexModifier;

        if ($armor->dex_modifier) {
            if ($armor->max_modifier > 0) {
                $chestDexMod = min($dexModifier, $armor->max_modifier);
            }
        } else {
            $chestDexMod = 0;
        }

        return $chestAc + $chestDexMod + $armor->armor_bonus;
    }

    /**
     * Computes additional AC bonus for an equipped non-chest item.
     *
     * @param int|null $itemId
     * @return int
     */
    private function getSlotItemArmorBonus(?int $itemId): int
    {
        if (!$itemId) {
            return 0;
        }

        $item = Item::findOne($itemId);
        if (!$item) {
            return 0;
        }

        if ($item->armor) {
            $armor = $item->armor;
            if ($armor->armor_bonus > 0) {
                return $armor->armor_bonus;
            }
            if ($armor->armor_class > 0) {
                return $armor->armor_class;
            }
        } elseif ($item->itemType && $item->itemType->name === 'Helmet') {
            return 1;
        }

        return 0;
    }

    /**
     * Calculates additional AC bonuses from non-chest equipped slots.
     *
     * @param PlayerBody $playerBody
     * @return int
     */
    private function getAdditionalSlotsArmorClass(PlayerBody $playerBody): int
    {
        $chestItemId = $playerBody->chest_item_id;
        $otherSlotIds = array_filter([
            'head' => $playerBody->head_item_id,
            'left_hand' => ($playerBody->left_hand_item_id !== $chestItemId) ? $playerBody->left_hand_item_id : null,
            'right_hand' => ($playerBody->right_hand_item_id !== $chestItemId) ? $playerBody->right_hand_item_id : null,
            'back' => $playerBody->back_item_id,
        ]);

        $additionalAc = 0;
        $processedItemIds = [];

        foreach ($otherSlotIds as $itemId) {
            if (!$itemId || in_array($itemId, $processedItemIds, true)) {
                continue;
            }
            $processedItemIds[] = $itemId;
            $additionalAc += $this->getSlotItemArmorBonus($itemId);
        }

        return $additionalAc;
    }

    /**
     * Recalculates and updates the player's Armor Class (AC) based on equipped items and DEX modifier.
     *
     * @param Player|null $player
     * @return int The updated Armor Class value.
     */
    public function updateArmorClass(?Player $player = null): int
    {
        $targetPlayer = $player ?? $this->player;
        if (!$targetPlayer) {
            return 10;
        }

        $dexModifier = $this->getDexterityModifier($targetPlayer);
        $playerBody = $targetPlayer->playerBody;

        if (!$playerBody) {
            $totalAc = 10 + $dexModifier;
        } else {
            $baseAc = $this->getChestBaseArmorClass($playerBody, $dexModifier);
            $additionalAc = $this->getAdditionalSlotsArmorClass($playerBody);
            $totalAc = $baseAc + $additionalAc;
        }

        if ($targetPlayer->armor_class !== $totalAc) {
            $targetPlayer->armor_class = $totalAc;
            $targetPlayer->save(false, ['armor_class', 'updated_at']);
        }

        return $totalAc;
    }
}
