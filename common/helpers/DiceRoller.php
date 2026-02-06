<?php

namespace common\helpers;

final class DiceRoller
{
    /**
     * Roll a DnD-style dice expression.
     * Examples: "1", "d8", "2d10", "2d20+3"
     */
    public static function roll(string $initialDiceToRoll): int
    {
        $diceToRoll = strtolower(trim($initialDiceToRoll));

        if (is_numeric($diceToRoll)) {
            return (int) $diceToRoll;
        }

        $matches = [];
        if (!preg_match('/^(\d*)d(\d+)([+-]\d+)?$/', $diceToRoll, $matches)) {
            throw new \InvalidArgumentException("Invalid dice format: {$diceToRoll}");
        }

        $numDice = $matches[1] === '' ? 1 : (int) $matches[1];
        $sides = (int) $matches[2];
        $modifier = isset($matches[3]) ? (int) $matches[3] : 0;

        $total = 0;
        for ($i = 0; $i < $numDice; $i++) {
            $total += random_int(1, $sides);
        }

        return $total + $modifier;
    }
}
