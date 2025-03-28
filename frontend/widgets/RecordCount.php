<?php

namespace frontend\widgets;

use yii\base\Widget;

class RecordCount extends Widget {

    public $model;
    public $count;
    public $adjective;
    public $actions;

    public function run() {
        return $this->render('record-count', [
                    'countLabel' => $this->setCountLabel(),
                    'actions' => $this->actions,
        ]);
    }

    private function setCountLabel() {
        $adjective = $this->adjective ?? 'available';
        switch ($this->count) {
            case 0:
                return 'There is no ' . $adjective . ' ' . $this->model . ' in the game';
            case 1:
                return 'There only one ' . $adjective . ' ' . $this->model . ' in the game';
            default:
                return 'List of the ' . $this->count . ' ' . $adjective . ' ' . $this->model . 's in the game';
        }
    }
}
