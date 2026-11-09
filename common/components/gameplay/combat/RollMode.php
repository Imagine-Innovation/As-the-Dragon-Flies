<?php

declare(strict_types=1);

namespace common\components\gameplay\combat;

/**
 * How a d20 is rolled: with advantage (best of two), disadvantage (worst of two) or normally.
 */
enum RollMode: int
{

    case DISADVANTAGE = -1;
    case NORMAL = 0;
    case ADVANTAGE = 1;
}
