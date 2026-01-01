<?php

namespace frontend\widgets;

use common\models\Player;
use yii\base\Widget;

class PlayerCharacteristics extends Widget
{

    public Player $player;
    public bool $embedded;

    public function run() {
        return $this->render('player-characteristics', [
                    'model' => $this->player,
                    'embedded' => $this->embedded ?? false,
        ]);
    }
}
