<?php

namespace frontend\widgets;

use yii\base\Widget;

class BuilderOnclick extends Widget {

    public $player;
    public $wizard;
    public $onclick;

    public function xxxrun() {
        $playerId = $this->player->id;
        $onclick = "";
        if ($this->wizard) {
            $onclick .= "PlayerBuilder.initWizard({$this->wizard});";
        }

        if ($this->onclick) {
            $onclick .= "PlayerBuilder.{$this->onclick}({$playerId});";
        }

        return $onclick ? 'onclick="' . $onclick . '"' : "";
    }

    public function run() {
        $playerId = $this->player->id;
        $onclick = 'onclick="PlayerBuilder.initWizard(' . "'" . $this->wizard . "'" . ');';

        if ($this->onclick) {
            $onclick .= "PlayerBuilder.{$this->onclick}({$playerId});";
        }

        return $onclick . '"';
    }
}
