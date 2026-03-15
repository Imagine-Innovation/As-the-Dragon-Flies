<?php

namespace backend\widgets;

use yii\base\Widget;

final class DbMonitorSuggestion extends Widget
{

    /** @var string[] */
    public array $suggestions = [];

    public function run(): string
    {
        return $this->render('db-monitor-suggestion', [
                    'suggestions' => $this->suggestions,
        ]);
    }
}
