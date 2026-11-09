<?php

declare(strict_types=1);

namespace common\components\gameplay;

use common\components\gameplay\combat\CoverDegree;
use common\components\gameplay\combat\CreatureSize;
use common\components\gameplay\combat\DeathSaveOutcome;
use common\components\gameplay\combat\RollMode;
use common\components\gameplay\combat\VitalStatus;
use common\helpers\DiceRoller;
use common\models\Player;
use common\models\Weapon;
use Yii;

/**
 * CombatManager
 *
 * Combat rules engine (see combat.md): attack rolls, advantage / disadvantage, cover,
 * damage, resistance / vulnerability, hit points, death saving throws, grapple / shove
 * contests, opportunity attacks, mounted and underwater combat.
 *
 * The core of the manager is stateless and database-free: it works on plain values and
 * typed arrays, which keeps the rules easy to test. All dice rolls go through
 * common\helpers\DiceRoller. Only the small "Player bridge" section
 * at the bottom touches ActiveRecord models, reusing PlayerManager for ability modifiers.
 * Further persistence (QuestLog, WebSocket events...) stays with the callers
 * (OutcomeManager, GameController...).
 *
 * @phpstan-type HitPointState array{
 *     hp: int,
 *     maxHp: int,
 *     tempHp: int,
 *     status: VitalStatus,
 *     deathSuccesses: int,
 *     deathFailures: int
 * }
 * @phpstan-type AttackResult array{
 *     hit: bool,
 *     critical: bool,
 *     fumble: bool,
 *     roll: int,
 *     rolls: list<int>,
 *     total: int,
 *     targetAc: int,
 *     mode: RollMode,
 *     targetable: bool
 * }
 * @phpstan-type RollFlags array{
 *     advantage: bool,
 *     disadvantage: bool,
 *     canAttack: bool,
 *     autoMiss: bool,
 *     reasons: list<string>
 * }
 * @phpstan-type RollModeContext array{
 *     attackerCanSeeTarget?: bool,
 *     targetCanSeeAttacker?: bool,
 *     isRanged?: bool,
 *     distance?: int,
 *     normalRange?: int|null,
 *     longRange?: int|null,
 *     hostileWithin5ft?: bool,
 *     isUnderwater?: bool,
 *     hasSwimSpeed?: bool,
 *     weaponName?: string|null,
 *     extraAdvantage?: bool,
 *     extraDisadvantage?: bool
 * }
 */
final class CombatManager extends BaseManager
{

    // --- Rule thresholds ---
    public const DEATH_SAVE_DC = 10;
    public const DEATH_SAVE_THRESHOLD = 3; // three successes or three failures
    public const STABILIZE_DC = 10;
    public const MOUNT_SAVE_DC = 10;
    public const NATURAL_CRITICAL = 20; // natural 20 on a d20
    public const NATURAL_FUMBLE = 1; // natural 1 on a d20

    // =========================================================================
    // Dice
    // =========================================================================

    /**
     * Combines advantage and disadvantage: if both apply, they cancel out.
     *
     * @param bool $hasAdvantage
     * @param bool $hasDisadvantage
     * @return RollMode
     */
    public function resolveRollMode(bool $hasAdvantage, bool $hasDisadvantage): RollMode
    {
        if ($hasAdvantage === $hasDisadvantage) {
            return RollMode::NORMAL;
        }
        return $hasAdvantage ? RollMode::ADVANTAGE : RollMode::DISADVANTAGE;
    }

    /**
     * Rolls a d20, with advantage or disadvantage if requested.
     *
     * @param RollMode $mode
     * @return array{roll: int, rolls: list<int>}
     */
    public function rollD20(RollMode $mode = RollMode::NORMAL): array
    {
        $rolls = [DiceRoller::roll('d20')];
        if ($mode !== RollMode::NORMAL) {
            $rolls[] = DiceRoller::roll('d20');
        }

        $roll = match ($mode) {
            RollMode::ADVANTAGE => max($rolls),
            RollMode::DISADVANTAGE => min($rolls),
            default => $rolls[0],
        };

        return ['roll' => $roll, 'rolls' => $rolls];
    }

    // =========================================================================
    // Attack modifiers
    // =========================================================================

    /**
     * Ability modifier used for a weapon attack.
     * Melee: Strength. Ranged: Dexterity. Finesse: the better of the two.
     * A melee weapon that is thrown uses Strength.
     *
     * @param int $strModifier
     * @param int $dexModifier
     * @param bool $isRanged
     * @param bool $isFinesse
     * @param bool $isThrownMeleeWeapon
     * @return int
     */
    public function weaponAbilityModifier(
        int $strModifier,
        int $dexModifier,
        bool $isRanged,
        bool $isFinesse = false,
        bool $isThrownMeleeWeapon = false,
    ): int
    {
        if ($isFinesse) {
            return max($strModifier, $dexModifier);
        }
        if ($isThrownMeleeWeapon) {
            return $strModifier;
        }
        return $isRanged ? $dexModifier : $strModifier;
    }

    /**
     * Total attack bonus: ability modifier + proficiency bonus (when proficient,
     * always true for spell attacks) + any extra bonus.
     *
     * @param int $abilityModifier
     * @param int $proficiencyBonus
     * @param bool $isProficient
     * @param int $extraBonus
     * @return int
     */
    public function attackBonus(int $abilityModifier, int $proficiencyBonus, bool $isProficient, int $extraBonus = 0): int
    {
        return $abilityModifier + ($isProficient ? $proficiencyBonus : 0) + $extraBonus;
    }

    /**
     * AC (and Dexterity saving throw) bonus granted by cover.
     *
     * @param CoverDegree $cover
     * @return int
     */
    public function coverBonus(CoverDegree $cover): int
    {
        return match ($cover) {
            CoverDegree::HALF => 2,
            CoverDegree::THREE_QUARTERS => 5,
            default => 0, // no cover; total cover cannot be targeted directly anyway
        };
    }

    /**
     * A target with total cover cannot be targeted directly.
     *
     * @param CoverDegree $cover
     * @return bool
     */
    public function isTargetable(CoverDegree $cover): bool
    {
        return $cover !== CoverDegree::TOTAL;
    }

    /**
     * Determines the attack roll mode (advantage / disadvantage) and whether the attack is possible.
     *
     * Each rule family lives in its own private method and returns an updated set of flags:
     * visibility, range, close combat and underwater combat.
     *
     * @param array<string, mixed> $context
     * @phpstan-param RollModeContext $context
     * @return array{mode: RollMode, canAttack: bool, autoMiss: bool, reasons: list<string>}
     */
    public function getAttackRollMode(array $context): array
    {
        $isRanged = $context['isRanged'] ?? false;
        $distance = $context['distance'] ?? 0;
        $normalRange = $context['normalRange'] ?? null;
        $longRange = $context['longRange'] ?? null;
        $weapon = $context['weaponName'] ?? null;

        $flags = $this->newRollFlags(
            $context['extraAdvantage'] ?? false,
            $context['extraDisadvantage'] ?? false,
        );

        $flags = $this->applyVisibilityRules(
            $flags,
            $context['attackerCanSeeTarget'] ?? true,
            $context['targetCanSeeAttacker'] ?? true,
        );

        if ($isRanged) {
            $flags = $this->applyRangeRules($flags, $distance, $normalRange, $longRange);
            $flags = $this->applyCloseCombatRule($flags, $context['hostileWithin5ft'] ?? null);
        }

        if ($context['isUnderwater'] ?? false) {
            $flags = $isRanged
                    ? $this->applyUnderwaterRangedRules($flags, $weapon, $distance, $normalRange)
                    : $this->applyUnderwaterMeleeRules($flags, $weapon, $context['hasSwimSpeed'] ?? null);
        }

        return [
            'mode' => $this->resolveRollMode($flags['advantage'], $flags['disadvantage']),
            'canAttack' => $flags['canAttack'],
            'autoMiss' => $flags['autoMiss'],
            'reasons' => $flags['reasons'],
        ];
    }

    /**
     * Creates the flag set accumulated by the attack roll mode rules.
     *
     * @param bool $advantage
     * @param bool $disadvantage
     * @return array<string, mixed>
     * @phpstan-return RollFlags
     */
    private function newRollFlags(bool $advantage = false, bool $disadvantage = false): array
    {
        return [
            'advantage' => $advantage,
            'disadvantage' => $disadvantage,
            'canAttack' => true,
            'autoMiss' => false,
            'reasons' => [],
        ];
    }

    /**
     * Unseen attackers and targets: attacking a target you cannot see gives disadvantage,
     * being unseen by the target gives advantage.
     *
     * @param array<string, mixed> $flags
     * @phpstan-param RollFlags $flags
     * @param bool $attackerCanSeeTarget
     * @param bool $targetCanSeeAttacker
     * @return array<string, mixed>
     * @phpstan-return RollFlags
     */
    private function applyVisibilityRules(array $flags, bool $attackerCanSeeTarget, bool $targetCanSeeAttacker): array
    {
        if (!$attackerCanSeeTarget) {
            $flags['disadvantage'] = true;
            $flags['reasons'][] = 'attacker_cannot_see_target';
        }
        if (!$targetCanSeeAttacker) {
            $flags['advantage'] = true;
            $flags['reasons'][] = 'target_cannot_see_attacker';
        }
        return $flags;
    }

    /**
     * Ranged attack range: impossible beyond the long range (or the single range),
     * disadvantage beyond the normal range.
     *
     * @param array<string, mixed> $flags
     * @phpstan-param RollFlags $flags
     * @param int $distance
     * @param int|null $normalRange
     * @param int|null $longRange
     * @return array<string, mixed>
     * @phpstan-return RollFlags
     */
    private function applyRangeRules(array $flags, int $distance, ?int $normalRange, ?int $longRange): array
    {
        $maxRange = $longRange ?? $normalRange;

        if ($maxRange !== null && $distance > $maxRange) {
            $flags['canAttack'] = false;
            $flags['reasons'][] = 'beyond_max_range';
        } elseif ($longRange !== null && $normalRange !== null && $distance > $normalRange) {
            $flags['disadvantage'] = true;
            $flags['reasons'][] = 'long_range';
        }
        return $flags;
    }

    /**
     * Ranged attack within 5 feet of a hostile creature that can see the attacker
     * and is not incapacitated: disadvantage.
     *
     * @param array<string, mixed> $flags
     * @phpstan-param RollFlags $flags
     * @param bool|null $hostileWithin5ft Null (unknown) is treated as no hostile creature nearby
     * @return array<string, mixed>
     * @phpstan-return RollFlags
     */
    private function applyCloseCombatRule(array $flags, ?bool $hostileWithin5ft): array
    {
        if ($hostileWithin5ft === true) {
            $flags['disadvantage'] = true;
            $flags['reasons'][] = 'ranged_in_close_combat';
        }
        return $flags;
    }

    /**
     * Underwater ranged attacks: automatic miss beyond the normal range, disadvantage
     * unless the weapon is a crossbow, a net or thrown like a javelin.
     *
     * @param array<string, mixed> $flags
     * @phpstan-param RollFlags $flags
     * @param string|null $weapon Weapon name (case-insensitive)
     * @param int $distance
     * @param int|null $normalRange
     * @return array<string, mixed>
     * @phpstan-return RollFlags
     */
    private function applyUnderwaterRangedRules(array $flags, ?string $weapon, int $distance, ?int $normalRange): array
    {
        if ($normalRange !== null && $distance > $normalRange) {
            $flags['autoMiss'] = true;
            $flags['reasons'][] = 'underwater_beyond_normal_range';
        }
        if (!$this->isUnderwaterRangedWeapon($weapon)) {
            $flags['disadvantage'] = true;
            $flags['reasons'][] = 'underwater_ranged_weapon';
        }
        return $flags;
    }

    /**
     * Underwater melee attacks: disadvantage unless the attacker has a swimming speed
     * or the weapon is a dagger, javelin, shortsword, spear or trident.
     *
     * @param array<string, mixed> $flags
     * @phpstan-param RollFlags $flags
     * @param string|null $weapon Weapon name (case-insensitive)
     * @param bool|null $hasSwimSpeed Null (unknown) is treated as no swimming speed
     * @return array<string, mixed>
     * @phpstan-return RollFlags
     */
    private function applyUnderwaterMeleeRules(array $flags, ?string $weapon, ?bool $hasSwimSpeed): array
    {
        if ($hasSwimSpeed !== true && !$this->isUnderwaterMeleeWeapon($weapon)) {
            $flags['disadvantage'] = true;
            $flags['reasons'][] = 'underwater_melee_weapon';
        }
        return $flags;
    }

    /**
     * Melee weapons that can be used without penalty underwater.
     *
     * @param string|null $weapon Weapon name (case-insensitive)
     * @return bool
     */
    private function isUnderwaterMeleeWeapon(?string $weapon): bool
    {
        return match (strtolower((string) $weapon)) {
            'dagger', 'javelin', 'shortsword', 'spear', 'trident' => true,
            default => false,
        };
    }

    /**
     * Ranged weapons that can be used without penalty underwater (within normal range).
     *
     * @param string|null $weapon Weapon name (case-insensitive)
     * @return bool
     */
    private function isUnderwaterRangedWeapon(?string $weapon): bool
    {
        return match (strtolower((string) $weapon)) {
            'crossbow', 'net', 'javelin', 'spear', 'trident', 'dart' => true,
            default => false,
        };
    }

    /**
     * Creatures and objects fully immersed in water have resistance to fire damage.
     *
     * @param bool $isFullyImmersed
     * @return bool
     */
    public function hasUnderwaterFireResistance(bool $isFullyImmersed): bool
    {
        return $isFullyImmersed;
    }

    // =========================================================================
    // Attack roll
    // =========================================================================

    /**
     * Resolves an attack roll: d20 + bonus against the AC (+ cover).
     * A natural 20 always hits (critical hit), a natural 1 always misses,
     * regardless of modifiers and AC.
     *
     * @param int $attackBonus
     * @param int $targetAc
     * @param CoverDegree $cover
     * @param RollMode $rollMode
     * @param bool $autoMiss Forces a miss (e.g. underwater attack beyond normal range)
     * @return array<string, mixed>
     * @phpstan-return AttackResult
     */
    public function makeAttackRoll(
        int $attackBonus,
        int $targetAc,
        CoverDegree $cover = CoverDegree::NONE,
        RollMode $rollMode = RollMode::NORMAL,
        bool $autoMiss = false,
    ): array
    {
        $effectiveAc = $targetAc + $this->coverBonus($cover);

        if (!$this->isTargetable($cover) || $autoMiss) {
            Yii::debug('*** debug *** CombatManager - makeAttackRoll: attack impossible or automatically missed');
            return $this->unrolledAttackResult($effectiveAc, $rollMode, $this->isTargetable($cover));
        }

        $result = $this->rolledAttackResult($attackBonus, $effectiveAc, $rollMode);
        Yii::debug(
                "*** debug *** CombatManager - makeAttackRoll: roll={$result['roll']}, total={$result['total']}, "
                . "ac={$effectiveAc}, hit=" . ($result['hit'] ? 'yes' : 'no'),
        );
        return $result;
    }

    /**
     * Result of an attack that is not rolled at all (total cover or automatic miss).
     *
     * @param int $effectiveAc
     * @param RollMode $rollMode
     * @param bool $targetable
     * @return array<string, mixed>
     * @phpstan-return AttackResult
     */
    private function unrolledAttackResult(int $effectiveAc, RollMode $rollMode, bool $targetable): array
    {
        return [
            'hit' => false,
            'critical' => false,
            'fumble' => false,
            'roll' => 0,
            'rolls' => [],
            'total' => 0,
            'targetAc' => $effectiveAc,
            'mode' => $rollMode,
            'targetable' => $targetable,
        ];
    }

    /**
     * Rolls the d20 of an attack and builds its result.
     *
     * @param int $attackBonus
     * @param int $effectiveAc AC including the cover bonus
     * @param RollMode $rollMode
     * @return array<string, mixed>
     * @phpstan-return AttackResult
     */
    private function rolledAttackResult(int $attackBonus, int $effectiveAc, RollMode $rollMode): array
    {
        $d20 = $this->rollD20($rollMode);
        $roll = $d20['roll'];
        $total = $roll + $attackBonus;

        return [
            'hit' => $this->isHit($roll, $total, $effectiveAc),
            'critical' => $roll === self::NATURAL_CRITICAL,
            'fumble' => $roll === self::NATURAL_FUMBLE,
            'roll' => $roll,
            'rolls' => $d20['rolls'],
            'total' => $total,
            'targetAc' => $effectiveAc,
            'mode' => $rollMode,
            'targetable' => true,
        ];
    }

    /**
     * Whether an attack roll hits: natural 20 always hits, natural 1 always misses,
     * otherwise the total must equal or exceed the AC.
     *
     * @param int $roll Natural d20 value
     * @param int $total Roll plus modifiers
     * @param int $targetAc
     * @return bool
     */
    private function isHit(int $roll, int $total, int $targetAc): bool
    {
        if ($roll === self::NATURAL_CRITICAL) {
            return true;
        }
        if ($roll === self::NATURAL_FUMBLE) {
            return false;
        }
        return $total >= $targetAc;
    }

    // =========================================================================
    // Damage
    // =========================================================================

    /**
     * Rolls damage dice and adds the modifier.
     * On a critical hit, ALL damage dice are rolled twice (the modifier is added only once).
     *
     * @param string $diceNotation Dice only (e.g. '1d8', '2d6'), without modifier
     * @param int $modifier
     * @param bool $critical
     * @return int Raw damage (never negative)
     */
    public function rollDamage(string $diceNotation, int $modifier = 0, bool $critical = false): int
    {
        $total = DiceRoller::roll($diceNotation);
        if ($critical) {
            $total += DiceRoller::roll($diceNotation);
        }
        return max(0, $total + $modifier);
    }

    /**
     * Unarmed strike damage: 1 + Strength modifier (bludgeoning).
     *
     * @param int $strModifier
     * @return int
     */
    public function unarmedStrikeDamage(int $strModifier): int
    {
        return max(0, 1 + $strModifier);
    }

    /**
     * Two-weapon fighting: the ability modifier is added to the bonus attack's damage
     * only if it is negative.
     *
     * @param int $abilityModifier
     * @return int
     */
    public function twoWeaponBonusDamageModifier(int $abilityModifier): int
    {
        return $abilityModifier < 0 ? $abilityModifier : 0;
    }

    /**
     * Adjusts damage: flat reductions first, then resistance (halved, rounded down),
     * then vulnerability (doubled). Multiple sources of the same effect count as one.
     *
     * Example: 25 damage, -5 aura, resistance => (25 - 5) / 2 = 10.
     *
     * @param int $damage
     * @param bool $resistant
     * @param bool $vulnerable
     * @param int $flatReduction
     * @return int
     */
    public function adjustDamage(int $damage, bool $resistant = false, bool $vulnerable = false, int $flatReduction = 0): int
    {
        $adjusted = max(0, $damage - max(0, $flatReduction));

        if ($resistant) {
            $adjusted = intdiv($adjusted, 2);
        }
        if ($vulnerable) {
            $adjusted *= 2;
        }
        return $adjusted;
    }

    // =========================================================================
    // Hit points
    // =========================================================================

    /**
     * Creates an initial hit point state.
     *
     * @param int $hp
     * @param int $maxHp
     * @param int $tempHp
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    public function newHitPointState(int $hp, int $maxHp, int $tempHp = 0): array
    {
        $maxHp = max(1, $maxHp);
        $hp = max(0, min($hp, $maxHp));

        return [
            'hp' => $hp,
            'maxHp' => $maxHp,
            'tempHp' => max(0, $tempHp),
            'status' => $hp > 0 ? VitalStatus::CONSCIOUS : VitalStatus::UNCONSCIOUS,
            'deathSuccesses' => 0,
            'deathFailures' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    private function kill(array $state): array
    {
        $state['hp'] = 0;
        $state['tempHp'] = 0;
        $state['status'] = VitalStatus::DEAD;
        return $state;
    }

    /**
     * Applies damage to a creature.
     *
     * - Temporary hit points are lost first.
     * - Instant death if the damage remaining after reaching 0 HP >= hit point maximum.
     * - At 0 HP: unconscious (dead for a monster, stable when knocked out).
     * - Damage taken at 0 HP: one death save failure (two on a critical hit);
     *   instant death if the damage >= hit point maximum.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $damage Damage already adjusted (resistances...)
     * @param bool $critical The damage comes from a critical hit
     * @param bool $knockOut Melee knock out: the target falls unconscious and stable
     * @param bool $dieAtZero Monster without death saves: dies at 0 HP
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    public function applyDamage(
        array $state,
        int $damage,
        bool $critical = false,
        bool $knockOut = false,
        bool $dieAtZero = false,
    ): array
    {
        if ($damage <= 0 || $state['status'] === VitalStatus::DEAD) {
            return $state;
        }

        $absorption = $this->absorbWithTemporaryHp($state, $damage);
        $state = $absorption['state'];
        $remaining = $absorption['remaining'];

        if ($remaining <= 0) {
            return $state;
        }

        return $state['hp'] === 0
                ? $this->applyDamageAtZeroHp($state, $remaining, $critical)
                : $this->applyDamageAboveZeroHp($state, $remaining, $knockOut, $dieAtZero);
    }

    /**
     * Temporary hit points absorb damage first.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $damage
     * @return array{state: array<string, mixed>, remaining: int}
     * @phpstan-return array{state: HitPointState, remaining: int}
     */
    private function absorbWithTemporaryHp(array $state, int $damage): array
    {
        $absorbed = min($state['tempHp'], $damage);
        $state['tempHp'] -= $absorbed;

        return ['state' => $state, 'remaining' => $damage - $absorbed];
    }

    /**
     * Damage taken by a creature already at 0 HP: one death save failure (two on a critical hit),
     * instant death if the damage reaches the hit point maximum.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $damage Damage remaining after temporary hit points
     * @param bool $critical
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    private function applyDamageAtZeroHp(array $state, int $damage, bool $critical): array
    {
        if ($damage >= $state['maxHp']) {
            return $this->kill($state);
        }

        $state['deathFailures'] += $critical ? 2 : 1;
        $state['status'] = VitalStatus::UNCONSCIOUS; // a stable creature is no longer stable

        return $state['deathFailures'] >= self::DEATH_SAVE_THRESHOLD ? $this->kill($state) : $state;
    }

    /**
     * Damage taken by a creature with hit points left: instant death on massive damage,
     * drop to 0 HP, or plain hit point loss.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $damage Damage remaining after temporary hit points
     * @param bool $knockOut
     * @param bool $dieAtZero
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    private function applyDamageAboveZeroHp(array $state, int $damage, bool $knockOut, bool $dieAtZero): array
    {
        $overflow = $damage - $state['hp'];

        if ($overflow >= $state['maxHp']) {
            return $this->kill($state); // instant death
        }

        if ($overflow >= 0) {
            return $this->dropToZeroHp($state, $knockOut, $dieAtZero);
        }

        $state['hp'] -= $damage;
        return $state;
    }

    /**
     * A creature drops to 0 HP: it dies (monster), falls stable (knock out) or unconscious.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param bool $knockOut
     * @param bool $dieAtZero
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    private function dropToZeroHp(array $state, bool $knockOut, bool $dieAtZero): array
    {
        if ($dieAtZero && !$knockOut) {
            return $this->kill($state);
        }

        $state['hp'] = 0;
        $state['status'] = $knockOut ? VitalStatus::STABLE : VitalStatus::UNCONSCIOUS;
        return $this->resetDeathSaves($state);
    }

    /**
     * Resets the death saving throw counters.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    private function resetDeathSaves(array $state): array
    {
        $state['deathSuccesses'] = 0;
        $state['deathFailures'] = 0;
        return $state;
    }

    /**
     * Heals a creature (capped at the hit point maximum). A dead creature cannot be healed.
     * Any healing wakes up a creature at 0 HP and resets its death saving throws.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $amount
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    public function heal(array $state, int $amount): array
    {
        if ($amount <= 0 || $state['status'] === VitalStatus::DEAD) {
            return $state;
        }

        $state['hp'] = min($state['maxHp'], $state['hp'] + $amount);
        $state['status'] = VitalStatus::CONSCIOUS;
        $state = $this->resetDeathSaves($state);
        return $state;
    }

    /**
     * Grants temporary hit points: they do not stack, the higher value is kept.
     * They neither heal nor stabilize a creature at 0 HP.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $amount
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    public function grantTemporaryHp(array $state, int $amount): array
    {
        if ($state['status'] === VitalStatus::DEAD) {
            return $state;
        }
        $state['tempHp'] = max($state['tempHp'], max(0, $amount));
        return $state;
    }

    /**
     * Death saving throw, rolled at the start of the turn of a creature at 0 HP
     * (unconscious, not stable).
     *
     * - >= 10: success; otherwise failure; natural 1: two failures; natural 20: regain 1 HP.
     * - Three successes: stable. Three failures: dead.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @return array{state: array<string, mixed>, roll: int, outcome: DeathSaveOutcome}
     * @phpstan-return array{state: HitPointState, roll: int, outcome: DeathSaveOutcome}
     */
    public function rollDeathSave(array $state): array
    {
        if ($state['status'] !== VitalStatus::UNCONSCIOUS || $state['hp'] !== 0) {
            return ['state' => $state, 'roll' => 0, 'outcome' => DeathSaveOutcome::NONE];
        }

        $roll = DiceRoller::roll('d20');

        if ($roll === self::NATURAL_CRITICAL) {
            return ['state' => $this->revive($state), 'roll' => $roll, 'outcome' => DeathSaveOutcome::REVIVED];
        }

        $recorded = $this->recordDeathSave($state, $roll);
        $resolved = $this->resolveDeathSaveThresholds($recorded['state'], $recorded['outcome']);

        return ['state' => $resolved['state'], 'roll' => $roll, 'outcome' => $resolved['outcome']];
    }

    /**
     * Natural 20 on a death saving throw: the creature regains 1 hit point and wakes up.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    private function revive(array $state): array
    {
        $state['hp'] = 1;
        $state['status'] = VitalStatus::CONSCIOUS;
        return $this->resetDeathSaves($state);
    }

    /**
     * Records a death saving throw as a success, a failure or a critical failure (two failures).
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $roll Natural d20 value (other than 20)
     * @return array{state: array<string, mixed>, outcome: DeathSaveOutcome}
     * @phpstan-return array{state: HitPointState, outcome: DeathSaveOutcome}
     */
    private function recordDeathSave(array $state, int $roll): array
    {
        if ($roll === self::NATURAL_FUMBLE) {
            $state['deathFailures'] += 2;
            return ['state' => $state, 'outcome' => DeathSaveOutcome::CRITICAL_FAILURE];
        }

        if ($roll >= self::DEATH_SAVE_DC) {
            $state['deathSuccesses']++;
            return ['state' => $state, 'outcome' => DeathSaveOutcome::SUCCESS];
        }

        $state['deathFailures']++;
        return ['state' => $state, 'outcome' => DeathSaveOutcome::FAILURE];
    }

    /**
     * Three failures kill the creature, three successes stabilize it.
     * Otherwise the recorded outcome is kept.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param DeathSaveOutcome $outcome Outcome of the roll that was just recorded
     * @return array{state: array<string, mixed>, outcome: DeathSaveOutcome}
     * @phpstan-return array{state: HitPointState, outcome: DeathSaveOutcome}
     */
    private function resolveDeathSaveThresholds(array $state, DeathSaveOutcome $outcome): array
    {
        if ($state['deathFailures'] >= self::DEATH_SAVE_THRESHOLD) {
            return ['state' => $this->kill($state), 'outcome' => DeathSaveOutcome::DEAD];
        }

        if ($state['deathSuccesses'] >= self::DEATH_SAVE_THRESHOLD) {
            $state['status'] = VitalStatus::STABLE;
            return ['state' => $this->resetDeathSaves($state), 'outcome' => DeathSaveOutcome::STABLE];
        }

        return ['state' => $state, 'outcome' => $outcome];
    }

    /**
     * Stabilization attempt (first aid action): DC 10 Wisdom (Medicine) check.
     *
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @param int $medicineCheckTotal Total result of the Medicine check
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    public function stabilize(array $state, int $medicineCheckTotal): array
    {
        if ($state['status'] !== VitalStatus::UNCONSCIOUS || $state['hp'] !== 0) {
            return $state;
        }
        if ($medicineCheckTotal >= self::STABILIZE_DC) {
            $state['status'] = VitalStatus::STABLE;
            $state = $this->resetDeathSaves($state);
        }
        return $state;
    }

    /**
     * A stable creature that is not healed regains 1 HP after 1d4 hours.
     *
     * @return int Number of hours before the creature regains 1 HP
     */
    public function stableRecoveryHours(): int
    {
        return DiceRoller::roll('1d4');
    }

    // =========================================================================
    // Contests: grapple and shove
    // =========================================================================

    /**
     * The target of a grapple / shove must be no more than one size larger than the attacker.
     *
     * @param CreatureSize $attackerSize
     * @param CreatureSize $targetSize
     * @return bool
     */
    public function isTargetSizeValid(CreatureSize $attackerSize, CreatureSize $targetSize): bool
    {
        return $targetSize->value <= $attackerSize->value + 1;
    }

    /**
     * Resolves a contest: the initiator's check against the defender's best check
     * (Athletics or Acrobatics, the defender chooses). Automatic success if the defender
     * is incapacitated. A tie does not make the initiator succeed.
     *
     * @param int $initiatorBonus
     * @param int $defenderBonusA E.g. Strength (Athletics)
     * @param int|null $defenderBonusB E.g. Dexterity (Acrobatics), null when not applicable
     * @param bool $defenderIncapacitated
     * @return array{success: bool, automatic: bool, initiatorTotal: int, defenderTotal: int}
     */
    public function resolveContest(
        int $initiatorBonus,
        int $defenderBonusA,
        ?int $defenderBonusB = null,
        bool $defenderIncapacitated = false,
    ): array
    {
        if ($defenderIncapacitated) {
            return ['success' => true, 'automatic' => true, 'initiatorTotal' => 0, 'defenderTotal' => 0];
        }

        $initiatorTotal = DiceRoller::roll('d20') + $initiatorBonus;
        $defenderBonus = $defenderBonusB === null ? $defenderBonusA : max($defenderBonusA, $defenderBonusB);
        $defenderTotal = DiceRoller::roll('d20') + $defenderBonus;

        return [
            'success' => $initiatorTotal > $defenderTotal,
            'automatic' => false,
            'initiatorTotal' => $initiatorTotal,
            'defenderTotal' => $defenderTotal,
        ];
    }

    /**
     * Grapple (replaces one attack of the Attack action; requires a free hand).
     *
     * @param int $athleticsBonus
     * @param int $targetAthleticsBonus
     * @param int $targetAcrobaticsBonus
     * @param CreatureSize $attackerSize
     * @param CreatureSize $targetSize
     * @param bool $targetIncapacitated
     * @param bool $hasFreeHand
     * @return array{success: bool, automatic: bool, initiatorTotal: int, defenderTotal: int, valid: bool}
     */
    public function grapple(
        int $athleticsBonus,
        int $targetAthleticsBonus,
        int $targetAcrobaticsBonus,
        CreatureSize $attackerSize = CreatureSize::MEDIUM,
        CreatureSize $targetSize = CreatureSize::MEDIUM,
        bool $targetIncapacitated = false,
        bool $hasFreeHand = true,
    ): array
    {
        if (!$hasFreeHand || !$this->isTargetSizeValid($attackerSize, $targetSize)) {
            return ['success' => false, 'automatic' => false, 'initiatorTotal' => 0, 'defenderTotal' => 0, 'valid' => false];
        }

        return $this->resolveContest($athleticsBonus, $targetAthleticsBonus, $targetAcrobaticsBonus, $targetIncapacitated)
                + ['valid' => true];
    }

    /**
     * Shove: knocks the target prone or pushes it 5 feet away.
     *
     * @param int $athleticsBonus
     * @param int $targetAthleticsBonus
     * @param int $targetAcrobaticsBonus
     * @param CreatureSize $attackerSize
     * @param CreatureSize $targetSize
     * @param bool $targetIncapacitated
     * @return array{success: bool, automatic: bool, initiatorTotal: int, defenderTotal: int, valid: bool}
     */
    public function shove(
        int $athleticsBonus,
        int $targetAthleticsBonus,
        int $targetAcrobaticsBonus,
        CreatureSize $attackerSize = CreatureSize::MEDIUM,
        CreatureSize $targetSize = CreatureSize::MEDIUM,
        bool $targetIncapacitated = false,
    ): array
    {
        return $this->grapple(
            $athleticsBonus,
            $targetAthleticsBonus,
            $targetAcrobaticsBonus,
            $attackerSize,
            $targetSize,
            $targetIncapacitated,
            true,
        );
    }

    /**
     * Escaping a grapple: Athletics or Acrobatics (best of both) against the grappler's Athletics.
     *
     * @param int $escapeeAthleticsBonus
     * @param int $escapeeAcrobaticsBonus
     * @param int $grapplerAthleticsBonus
     * @return array{success: bool, automatic: bool, initiatorTotal: int, defenderTotal: int}
     */
    public function escapeGrapple(int $escapeeAthleticsBonus, int $escapeeAcrobaticsBonus, int $grapplerAthleticsBonus): array
    {
        return $this->resolveContest(max($escapeeAthleticsBonus, $escapeeAcrobaticsBonus), $grapplerAthleticsBonus);
    }

    /**
     * Speed while dragging or carrying a grappled creature: halved, unless the creature
     * is at least two sizes smaller.
     *
     * @param int $speed
     * @param CreatureSize $grapplerSize
     * @param CreatureSize $grappledSize
     * @return int
     */
    public function speedWhileDragging(int $speed, CreatureSize $grapplerSize, CreatureSize $grappledSize): int
    {
        return ($grapplerSize->value - $grappledSize->value) >= 2 ? $speed : intdiv($speed, 2);
    }

    // =========================================================================
    // Opportunity attacks
    // =========================================================================

    /**
     * An opportunity attack is possible when a visible hostile creature moves out of the
     * attacker's reach and the attacker has its reaction available. Disengaging, teleporting
     * or being moved without spending movement / action / reaction avoids it.
     *
     * @param bool $canSeeMover
     * @param bool $movedOutOfReach
     * @param bool $reactionAvailable
     * @param bool $moverDisengaged
     * @param bool $moverTeleportedOrForcedMoved
     * @return bool
     */
    public function canMakeOpportunityAttack(
        bool $canSeeMover,
        bool $movedOutOfReach,
        bool $reactionAvailable,
        bool $moverDisengaged = false,
        bool $moverTeleportedOrForcedMoved = false,
    ): bool
    {
        return $canSeeMover
                && $movedOutOfReach
                && $reactionAvailable
                && !$moverDisengaged
                && !$moverTeleportedOrForcedMoved;
    }

    // =========================================================================
    // Mounted combat
    // =========================================================================

    /**
     * Movement cost to mount or dismount: half of the speed.
     *
     * @param int $speed
     * @return int
     */
    public function mountMovementCost(int $speed): int
    {
        return intdiv(max(0, $speed), 2);
    }

    /**
     * Whether a creature can mount: it needs enough remaining movement and a speed above 0.
     *
     * @param int $speed
     * @param int $remainingMovement
     * @return bool
     */
    public function canMount(int $speed, int $remainingMovement): bool
    {
        return $speed > 0 && $remainingMovement >= $this->mountMovementCost($speed);
    }

    /**
     * A mount must be willing and at least one size larger than its rider.
     *
     * @param CreatureSize $riderSize
     * @param CreatureSize $mountSize
     * @param bool $isWilling
     * @return bool
     */
    public function isValidMount(CreatureSize $riderSize, CreatureSize $mountSize, bool $isWilling = true): bool
    {
        return $isWilling && $mountSize->value >= $riderSize->value + 1;
    }

    /**
     * DC 10 Dexterity saving throw to avoid falling when the mount is moved against its
     * will or knocked prone.
     *
     * @param int $dexSavingThrowModifier
     * @return array{success: bool, roll: int, total: int, dc: int}
     */
    public function rollMountFallSave(int $dexSavingThrowModifier): array
    {
        $roll = DiceRoller::roll('d20');
        $total = $roll + $dexSavingThrowModifier;

        return [
            'success' => $total >= self::MOUNT_SAVE_DC,
            'roll' => $roll,
            'total' => $total,
            'dc' => self::MOUNT_SAVE_DC,
        ];
    }

    /**
     * Actions available to a controlled mount.
     * An independent mount keeps its own actions (empty list = no restriction).
     *
     * @param bool $isControlled
     * @return list<string>
     */
    public function mountAvailableActions(bool $isControlled): array
    {
        return $isControlled ? ['dash', 'disengage', 'dodge'] : [];
    }

    /**
     * A mount can be controlled only if it has been trained to accept a rider.
     * Intelligent creatures (e.g. dragons) always act independently.
     *
     * @param bool $isTrained
     * @param bool $isIntelligent
     * @return bool
     */
    public function canControlMount(bool $isTrained, bool $isIntelligent = false): bool
    {
        return $isTrained && !$isIntelligent;
    }

    // =========================================================================
    // Player bridge (ActiveRecord models)
    // =========================================================================

    /**
     * Builds the attack profile of a player wielding a weapon.
     *
     * Reuses PlayerManager::getAbilityModifier() for STR / DEX, Level::proficiency_bonus for the
     * proficiency bonus and Player::isProficient() for weapon proficiency.
     * Assumption: Weapon::range_min is the normal range and Weapon::range_max the long range.
     *
     * @param Player $player
     * @param Weapon $weapon
     * @param bool $isThrown Whether the weapon is thrown instead of being used as is
     * @param bool $twoHanded Use the versatile damage dice when the weapon has any
     * @return array{
     *     attackBonus: int,
     *     abilityModifier: int,
     *     damageDice: string,
     *     damageModifier: int,
     *     isRanged: bool,
     *     normalRange: int|null,
     *     longRange: int|null,
     *     weaponName: string
     * }
     */
    public function buildWeaponAttack(Player $player, Weapon $weapon, bool $isThrown = false, bool $twoHanded = false): array
    {
        $strModifier = PlayerManager::getAbilityModifier($player->id, 'STR');
        $dexModifier = PlayerManager::getAbilityModifier($player->id, 'DEX');

        $isMelee = (bool) $weapon->is_melee;
        $isRanged = (bool) $weapon->is_ranged || ($isThrown && $isMelee);

        $abilityModifier = $this->weaponAbilityModifier(
            $strModifier,
            $dexModifier,
            $isRanged,
            (bool) $weapon->is_finesse,
            $isThrown && $isMelee,
        );

        $proficiencyBonus = (int) ($player->level->proficiency_bonus ?? 0);
        $isProficient = (bool) $player->isProficient($weapon->item_id);

        $damageDice = $twoHanded && $weapon->versatile_dice ? $weapon->versatile_dice : ($weapon->damage_dice ?? '1d4');

        return [
            'attackBonus' => $this->attackBonus($abilityModifier, $proficiencyBonus, $isProficient),
            'abilityModifier' => $abilityModifier,
            'damageDice' => $damageDice,
            'damageModifier' => $abilityModifier,
            'isRanged' => $isRanged,
            'normalRange' => $weapon->range_min ?? null,
            'longRange' => $weapon->range_max ?? null,
            'weaponName' => (string) ($weapon->item->name ?? ''),
        ];
    }

    /**
     * Builds a hit point state from a player's persisted hit points.
     * Temporary hit points and death save counters are not persisted on Player.
     *
     * @param Player $player
     * @param int $tempHp
     * @return array<string, mixed>
     * @phpstan-return HitPointState
     */
    public function hitPointStateFromPlayer(Player $player, int $tempHp = 0): array
    {
        return $this->newHitPointState((int) $player->hit_points, (int) $player->max_hit_points, $tempHp);
    }

    /**
     * Persists the hit points of a hit point state on the player,
     * with the same updateAll() approach as PlayerManager.
     *
     * @param Player $player
     * @param array<string, mixed> $state
     * @phpstan-param HitPointState $state
     * @return void
     */
    public function persistHitPoints(Player $player, array $state): void
    {
        Yii::debug("*** debug *** CombatManager - persistHitPoints: player={$player->id}, hp={$state['hp']}, status={$state['status']->name}");

        Player::updateAll(['hit_points' => $state['hp']], ['id' => $player->id]);

        // Keep the in-memory model consistent without marking the attribute as dirty
        $player->hit_points = $state['hp'];
        $player->setOldAttribute('hit_points', $state['hp']);
    }
}
