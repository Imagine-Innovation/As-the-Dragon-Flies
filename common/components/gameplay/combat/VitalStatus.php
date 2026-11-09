<?php

declare(strict_types=1);

namespace common\components\gameplay\combat;

/**
 * Vital state of a creature regarding its hit points.
 */
enum VitalStatus: int
{

    case CONSCIOUS = 1;
    case UNCONSCIOUS = 2;
    case STABLE = 3;
    case DEAD = 4;
}
