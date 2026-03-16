<?php

namespace backend\widgets;

use Yii;
use yii\base\Widget;

class Kpi extends Widget
{

    public string $title = 'KPI';
    public ?string $backgroundStyle = null;
    public ?string $containerName = null;
    public ?string $icon = null;
    public ?string $badge = null;
    public ?string $value = null;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        return $this->render('kpi', [
                    'backgroundStyle' => $this->backgroundStyle,
                    'title' => $this->title,
                    'containerName' => $this->containerName,
                    'icon' => $this->icon,
                    'badge' => $this->badge,
                    'value' => $this->value ?? '?',
        ]);
    }
}
