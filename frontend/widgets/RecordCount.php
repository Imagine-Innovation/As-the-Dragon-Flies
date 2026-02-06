<?php

namespace frontend\widgets;

use yii\base\Widget;

class RecordCount extends Widget
{
    public string $model;
    public int $count;
    public string $adjective;

    /** @var array<string, mixed>|null $actions */
    public ?array $actions = null;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        return $this->render('record-count', [
            'countLabel' => $this->setCountLabel(),
            'actions' => $this->actions,
        ]);
    }

    /**
     *
     * @return string
     */
    private function setCountLabel(): string
    {
        $adjective = $this->adjective ?? 'available';

        return match ($this->count) {
            0 => "There is no {$adjective} {$this->model} in the game",
            1 => "There is only one {$adjective} {$this->model} in the game",
            default => "List of the {$this->count} {$adjective} {$this->model}'s in the game",
        };
    }
}
