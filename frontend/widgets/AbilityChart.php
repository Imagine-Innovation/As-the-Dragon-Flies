<?php

namespace frontend\widgets;

use yii\base\Widget;

class AbilityChart extends Widget {

    public $id;
    public $score;
    public $code;
    public $bonus;

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
