<?php

namespace common\components;

class DiceRoller {

    /**
     * Roll a single die of any number of sides.
     *
     * @param int $sides Number of sides on the die.
     * @return int The result of the die roll.
     */
    public static function rollDie($sides) {
        return mt_rand(1, $sides);
    }

    /**
     * Roll multiple dice of the same type.
     *
     * @param int $numberOfDice Number of dice to roll.
     * @param int $sides Number of sides on the dice.
     * @return array An array of the rolled values.
     */
    public static function rollMultipleDice($numberOfDice, $sides) {
        $rolls = [];
        for ($i = 0; $i < $numberOfDice; $i++) {
            $rolls[] = self::rollDie($sides);
        }
        return $rolls;
    }

    /**
     * Roll dice with a modifier, e.g., "1d6+3".
     *
     * @param string $notation The dice notation (e.g., "2d8+4").
     * @return int The total result of the dice rolls plus the modifier.
     */
    public static function roll($notation) {
        $matches = [];
        preg_match('/(\d+)d(\d+)([+-]\d+)?/', $notation, $matches);

        $numberOfDice = (int) $matches[1];
        $sides = (int) $matches[2];
        $modifier = isset($matches[3]) ? (int) $matches[3] : 0;

        $rolls = self::rollMultipleDice($numberOfDice, $sides);

        $sum = array_sum($rolls) + $modifier;

        return $sum;
    }
}
