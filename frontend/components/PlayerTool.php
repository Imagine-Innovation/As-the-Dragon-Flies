<?php

namespace frontend\components;

use common\models\Race;
use common\models\Wizard;
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
}
