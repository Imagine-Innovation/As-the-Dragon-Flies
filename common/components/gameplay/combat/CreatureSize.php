<?php

declare(strict_types=1);

namespace common\components\gameplay\combat;

/**
 * Creature size. Values are ordered so that sizes can be compared arithmetically.
 */
enum CreatureSize: int
{

    case TINY = 0;
    case SMALL = 1;
    case MEDIUM = 2;
    case LARGE = 3;
    case HUGE = 4;
    case GARGANTUAN = 5;

    /**
     * Converts a size code (such as Race::size: "M" for Medium, "S" for Small) to an enum case.
     * Unknown codes default to Medium.
     *
     * @param string $code T, S, M, L, H or G
     * @return self
     */
    public static function fromCode(string $code): self
    {
        return match (strtoupper($code)) {
            'T' => self::TINY,
            'S' => self::SMALL,
            'L' => self::LARGE,
            'H' => self::HUGE,
            'G' => self::GARGANTUAN,
            default => self::MEDIUM,
        };
    }
}
