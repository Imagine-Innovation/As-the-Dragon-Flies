<?php

namespace frontend\widgets;

use yii\base\Widget;

class PlayerCharacteristics extends Widget {

    public $player;
    public $embedded;

    public function run() {
        return $this->render('player-characteristics', [
                        'model' => $this->player,
                        'embedded' => $this->embedded ?? false,
        ]);
    }
}
