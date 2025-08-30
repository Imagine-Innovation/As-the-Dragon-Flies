<?php

namespace frontend\widgets;

use common\helpers\Utilities;
use frontend\helpers\ActionButtonsConfig;
use yii\base\Widget;

class ActionButtons extends Widget
{

    public $model;
    public $isOwner;
    public $mode;

    public function run() {
        $modelName = Utilities::modelName($this->model);
        $actions = ActionButtonsConfig::getActions($modelName, $this->model->status);

        $widgetView = $this->mode == "table" ? 'action-buttons-table' : 'action-buttons-icon';

        return $this->render($widgetView, [
                    'model' => $this->model,
                    'modelName' => $modelName,
                    'actions' => $actions,
                    'isOwner' => $this->isOwner ?? true,
        ]);
    }
}
