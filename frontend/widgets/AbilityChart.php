<?php

namespace frontend\widgets;

use yii\base\Widget;

class AbilityChart extends Widget
{

    public int $id;
    public int $score;
    public string $code;
    public int $bonus;

    public function run() {
        $abilityScore = $this->score + $this->bonus;
        $tmp = ($abilityScore - 10) / 2;
        $modifier = $tmp >= 0 ? floor($tmp) : ceil($tmp);

        return $this->render('ability-chart', [
                    'id' => $this->id,
                    'score' => $this->score,
                    'bonus' => $this->bonus,
                    'code' => $this->code,
                    'modifier' => $modifier,
        ]);
    }
}
