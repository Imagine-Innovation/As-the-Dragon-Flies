<?php

namespace frontend\widgets;

use common\models\Player;
use yii\base\Widget;
use yii\db\ActiveRecord;

class BuilderTab extends Widget
{

    public Player $player;

    /** @var array<string, mixed> $tabContent */
    public array $tabContent;

    /**
     *
     * @return string
     */
    public function run(): string {
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

    /**
     *
     * @param string $modelName
     * @return ActiveRecord
     */
    private function getModels(string $modelName): ActiveRecord {
        $model = "common\\models\\" . $modelName;
        return $model::find()->all();
    }
}
