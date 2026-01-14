<?php

namespace frontend\widgets;

use common\helpers\MixedHelper;
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
    public function run(): string {
        // Narrow the type of status if it's not strictly an int or string in ActiveRecord
        $status = MixedHelper::toInt($this->model->getAttribute('status'));
        $modelName = Utilities::modelName($this->model) ?? 'Unknown';
        $actions = ActionButtonsConfig::getActions($modelName, $status);

        $widgetView = ($this->mode === 'table') ? 'action-buttons-table' : 'action-buttons-icon';

        return $this->render($widgetView, [
                    'model' => $this->model,
                    'modelName' => $modelName,
                    'actions' => $actions,
                    'isOwner' => $this->isOwner ?? true,
        ]);
    }
}
