<?php

namespace frontend\widgets;

use common\helpers\Utilities;
use frontend\helpers\ActionButtonsConfig;
use yii\base\Widget;

/** @template T of \yii\db\ActiveRecord */
class ActionButtons extends Widget
{
    /** @var T */
    public \yii\db\ActiveRecord $model;
    public ?bool $isOwner = null;
    public ?string $mode = null;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        // Narrow the type of status if it's not strictly an int or string in ActiveRecord
        $statusProperty = $this->model->getAttribute('status');
        $status = is_numeric($statusProperty) ? (int) $statusProperty : 0;
        $controller = Utilities::getController($this->model) ?? 'Unknown';
        $actions = ActionButtonsConfig::getActions($controller, $status);

        $widgetView = $this->mode === 'table' ? 'action-buttons-table' : 'action-buttons-icon';

        return $this->render($widgetView, [
            'model' => $this->model,
            'controller' => $controller,
            'actions' => $actions,
            'isOwner' => $this->isOwner ?? true,
        ]);
    }
}
