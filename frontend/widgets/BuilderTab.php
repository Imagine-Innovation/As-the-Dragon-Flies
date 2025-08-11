<?php

namespace frontend\widgets;

use yii\base\Widget;

class BuilderTab extends Widget {

    public $player;
    public $tabContent;

    public function run() {
        $tab = $this->tabContent;

        if ($tab['model_name']) {
            return $this->render('builder-tab', [
                        'models' => $this->getModels($tab['model_name']),
                        'field_name' => $tab['field_name'],
                        'paragraphs' => $tab['paragraphs'],
            ]);
        } else {
            $partialView = '@app/views/player-builder/snippets/' . $tab['anchor'] . '.php';
            return $this->renderFile($partialView, [
                        'model' => $this->player,
                        'paragraphs' => $tab['paragraphs']
            ]);
        }
    }

    private function getModels($modelName) {
        $model = "common\\models\\" . $modelName;
        return $model::find()->all();
    }
}
