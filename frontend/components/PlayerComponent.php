<?php

namespace frontend\components;

use common\models\ClassItemProficiency;
use common\models\ItemCategory;
use common\models\PlayerAbility;
use common\models\Weapon;
use Yii;
use yii\helpers\ArrayHelper;

class PlayerComponent
{

    const EMPTY_ABILITIES = ['STR' => [], 'DEX' => [], 'CON' => [], 'INT' => [], 'WIS' => [], 'CHA' => []];

    /**
     * Calculates ability modifier based on ability score
     *
     * @param int $abilityScore
     * @return int
     */
    public static function calcAbilityModifier(int $abilityScore): int {
        return $abilityScore >= 10 ? intval(floor(($abilityScore - 10) / 2)) : intval(ceil(($abilityScore - 10) / 2));
    }

    /**
     * Checks if a player is proficient with a specific item.
     *
     * @param int $classId The ID of the player class.
     * @param int $itemId The ID of the item to check proficiency for.
     * @return bool Returns true if the player is proficient with the item, false if not,
     *              or null if the player parameter is null.
     */
    public static function isProficient(int $classId, int $itemId): bool {
        // Check whether the player's class gives a direct proficiency for the item.
        Yii::debug("*** Debug *** isProficient({$classId}, {$itemId})");
        $proficientForItem = ClassItemProficiency::findOne(['class_id' => $classId, 'item_id' => $itemId]);
        if ($proficientForItem) {
            Yii::debug("*** Debug *** isProficient is proficient for item");
            return true;
        }

        // If not, check that the item belongs to a category for which
        // the class gives the player a particular proficiency.
        $categoryIds = ClassItemProficiency::find()
                ->select('category_id')
                ->where(['class_id' => $classId])
                ->andWhere(['is not', 'category_id', null])
                ->all();

        $proficient = ItemCategory::findAll(['item_id' => $itemId, 'category_id' => $categoryIds]);

        Yii::debug("*** Debug *** isProficient return " . ($proficient ? 'true' : 'false'));
        return $proficient ? true : false;
    }

    /**
     * Gathers the different ability and saving throw data into a simple associative array
     *
     * @param PlayerAbility[] $playerAbilities
     * @param int $proficiencyModifier
     * @return array
     */
    public static function getAbilitiesAndSavingThrow(array $playerAbilities, int $proficiencyModifier): array {
        // set the initialization order to the common way of displaying abilities
        $abilities = self::EMPTY_ABILITIES;

        foreach ($playerAbilities as $playerAbility) {
            $ability = $playerAbility->ability;
            $savingThrow = $playerAbility->modifier + ($playerAbility->is_saving_throw ? $proficiencyModifier : 0);
            $playerData = [
                'code' => $ability->code,
                'name' => $ability->name,
                'score' => $playerAbility->score,
                'modifier' => $playerAbility->modifier,
                'savingThrow' => $savingThrow
            ];
            $abilities[$ability->code] = $playerData;
        }

        return $abilities;
    }

    /**
     * Retrieve the player's ability modifier for a specific code
     *
     * @param int $playerId
     * @param string $abilityCode
     * @return int
     */
    public static function getAbilityModifier(int $playerId, string $abilityCode): int {
        $playerAbility = PlayerAbility::find()
                ->joinWith('player')
                ->joinWith('ability')
                ->where(['player.id' => $playerId, 'ability.code' => $abilityCode])
                ->one();

        return $playerAbility->modifier ?? 0;
    }

    /**
     * Get the weapon's attack modifier and damage
     *
     * @param int $playerId
     * @param int $weaponId
     * @param int $proficiencyModifier
     * @return array
     */
    public static function getPlayerWeaponProperties(int $playerId, int $weaponId, int $proficiencyModifier): array {

        $weapon = Weapon::findOne(['item_id' => $weaponId]);
        if (!$weapon) {
            return ['attackModifier' => null, 'damage' => null];
        }

        if ($weapon->is_finesse) {
            $strength = self::getAbilityModifier($playerId, 'STR');
            $dexterity = self::getAbilityModifier($playerId, 'DEX');
            $abilityModifier = max($dexterity, $strength);
        } else {
            $categories = ArrayHelper::getColumn($weapon->categories, 'name');
            $abilityCode = (in_array("Ranged Weapon", $categories)) ? 'DEX' : 'STR';
            $abilityModifier = self::getAbilityModifier($playerId, $abilityCode);
        }

        return [
            'attackModifier' => $abilityModifier + $proficiencyModifier,
            'damage' => "{$weapon->damage_dice}+{$abilityModifier}"
        ];
    }
}
