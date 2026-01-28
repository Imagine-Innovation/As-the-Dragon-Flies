<?php

namespace frontend\widgets;

use common\models\Player;
use yii\base\Widget;

class PlayerCharacteristics extends Widget
{

    public Player $player;
    public bool $embedded;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        return $this->render('player-characteristics', [
                    'model' => $this->player,
                    'embedded' => $this->embedded ?? false,
        ]);
    }
}
