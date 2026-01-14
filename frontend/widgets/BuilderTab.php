<?php

namespace frontend\widgets;

use common\models\Player;
use yii\base\Widget;

/** @template T of \yii\db\ActiveRecord */
class BuilderTab extends Widget
{

    public Player $player;

    /** @var array{model_name: class-string<T>, field_name: string, anchor: string, paragraphs: array<string>} $tabContent */
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
     * @param class-string<T> $modelName
     * @return T[]
     */
    private function getModels(string $modelName): array {
        /** @var class-string<T> $fullyQualifiedName */
        $fullyQualifiedName = "common\\models\\" . $modelName;
        return $fullyQualifiedName::find()->all();
    }
}
