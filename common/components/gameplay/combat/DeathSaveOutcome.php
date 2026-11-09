<?php

declare(strict_types=1);

namespace common\components\gameplay\combat;

/**
 * Result of a death saving throw.
 */
enum DeathSaveOutcome: int
{

    case NONE = 0; // no death saving throw was required
    case SUCCESS = 1;
    case FAILURE = 2;
    case CRITICAL_FAILURE = 3; // natural 1: counts as two failures
    case REVIVED = 4; // natural 20: regain 1 hit point
    case STABLE = 5; // third success
    case DEAD = 6; // third failure
}
