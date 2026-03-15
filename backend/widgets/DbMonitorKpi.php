<?php

namespace backend\widgets;

use yii\base\Widget;

final class DbMonitorKpi extends Widget
{

    public string $label = '';

    /** @var int|string */
    public $value = '';

    public function run(): string
    {
        return $this->render('db-monitor-kpi', [
                    'label' => $this->label,
                    'value' => $this->value,
        ]);
    }
}
