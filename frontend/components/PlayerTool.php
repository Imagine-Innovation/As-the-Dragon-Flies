<?php

namespace frontend\components;

use common\models\ClassItemProficiency;
use common\models\ItemCategory;
use common\models\PlayerAbility;
use Yii;

class PlayerTool {

    /**
     * Calculates ability modifier based on ability score
     *
     * @param int $abilityScore
     * @return int
     */
    public static function calcAbilityModifier(int $abilityScore): int {
        return $abilityScore >= 10 ? floor(($abilityScore - 10) / 2) : ceil(($abilityScore - 10) / 2);
    }

    /**
     * Checks if a player is proficient with a specific item.
     *
     * @param int $classId The ID of the player class.
     * @param int $item_id The ID of the item to check proficiency for.
     * @return bool|null Returns true if the player is proficient with the item, false if not,
     *                   or null if the player parameter is null.
     */
    public static function isProficient(int $classId, int $item_id): bool {
        // Check whether the player's class gives a direct proficiency for the item.
        Yii::debug("*** Debug *** isProficient({$classId}, {$item_id})");
        $proficientForItem = ClassItemProficiency::findOne(['class_id' => $classId, 'item_id' => $item_id]);
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

        $proficient = ItemCategory::findAll(['item_id' => $item_id, 'category_id' => $categoryIds]);

        Yii::debug("*** Debug *** isProficient return " . ($proficient ? 'true' : 'false'));
        return $proficient ? true : false;
    }

    public static function getAbilitiesAndSavingThrow(array $playerAbilities, int $proficiencyBonus): array {
        // set the initialization order to the common way of displaying abilities
        $abilities = ['STR' => [], 'DEX' => [], 'CON' => [], 'INT' => [], 'WIS' => [], 'CHA' => []];

        foreach ($playerAbilities as $playerAbility) {
            $ability = $playerAbility->ability;
            $savingThrow = $playerAbility->modifier + ($playerAbility->is_saving_throw ? $proficiencyBonus : 0);
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
}
