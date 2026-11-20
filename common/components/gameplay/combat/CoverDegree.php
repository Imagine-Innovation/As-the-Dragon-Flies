<?php

declare(strict_types=1);

namespace common\components\gameplay\combat;

/**
 * Degree of cover protecting a target (see "Cover" in combat.md).
 */
enum CoverDegree: int
{

    case NONE = 0;
    case HALF = 1;
    case THREE_QUARTERS = 2;
    case TOTAL = 3;
}
