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
    public static function calcAbilityModifier(int $abilityScore): int
    {
        return $abilityScore >= 10 ? intval(floor(($abilityScore - 10) / 2)) : intval(ceil(($abilityScore - 10) / 2));
    }

    /**
     * Gathers the different ability and saving throw data into a simple associative array
     *
     * @param PlayerAbility[] $playerAbilities
     * @param int $proficiencyModifier
     * @return non-empty-array<string, array{}|array{code: string, name: string, score: int, modifier: int, savingThrow: int}>
     */
    public static function getAbilitiesAndSavingThrow(array $playerAbilities, int $proficiencyModifier): array
    {
        // set the initialization order to the common way of displaying abilities
        $abilities = self::EMPTY_ABILITIES;

        foreach ($playerAbilities as $playerAbility) {
            $ability = $playerAbility->ability;
            $savingThrow = $playerAbility->modifier + ($playerAbility->is_saving_throw
                        ? $proficiencyModifier : 0);
            $playerData = [
                'code' => $ability->code,
                'name' => $ability->name,
                'score' => $playerAbility->score,
                'modifier' => $playerAbility->modifier,
                'savingThrow' => $savingThrow,
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
    public static function getAbilityModifier(int $playerId, string $abilityCode): int
    {
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
     * @return array{
     * attackModifier: int|null,
     * damage: string|null,
     * isTwoHanded: int|null
     * }
     */
    public static function getPlayerWeaponProperties(int $playerId, int $weaponId, int $proficiencyModifier): array
    {
        $weapon = Weapon::findOne(['item_id' => $weaponId]);
        if ($weapon === null) {
            return ['attackModifier' => null, 'damage' => null, 'isTwoHanded' => null];
        }

        if ($weapon->is_finesse) {
            $strength = self::getAbilityModifier($playerId, 'STR');
            $dexterity = self::getAbilityModifier($playerId, 'DEX');
            $abilityModifier = max($dexterity, $strength);
        } else {
            $categories = ArrayHelper::getColumn($weapon->categories, 'name');
            $abilityCode = in_array('Ranged Weapon', $categories) ? 'DEX' : 'STR';
            $abilityModifier = self::getAbilityModifier($playerId, $abilityCode);
        }

        return [
            'attackModifier' => $abilityModifier + $proficiencyModifier,
            'damage' => "{$weapon->damage_dice}+{$abilityModifier}",
            'isTwoHanded' => $weapon->is_two_handed ? 1 : 0,
        ];
    }
}
