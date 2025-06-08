<?php

namespace frontend\widgets;

use common\components\AppStatus;
use common\helpers\Utilities;
use Yii;
use yii\base\Widget;

class ActionButtons extends Widget {

    public $model;
    public $isOwner;
    public $mode;

    public function run() {
        $modelName = Utilities::modelName($this->model);
        $actions = [
            AppStatus::DELETED->value => [
                [
                    'tooltip' => 'Restore',
                    'route' => $modelName,
                    'verb' => 'restore',
                    'icon' => 'arrow-left-square',
                    'admin' => true,
                    'player' => false,
                    'owner' => false,
                    'modelName' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
            ],
            AppStatus::INACTIVE->value => [
                [
                    'tooltip' => 'View',
                    'route' => $modelName,
                    'verb' => 'view',
                    'icon' => 'info-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'modelName' => ['rule'],
                    'table' => true,
                    'view' => false,
                ],
                [
                    'tooltip' => 'Validate',
                    'route' => $modelName,
                    'verb' => 'validate',
                    'icon' => 'check-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'modelName' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
                [
                    'tooltip' => 'Delete',
                    'route' => $modelName,
                    'verb' => 'delete',
                    'icon' => 'x-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'modelName' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
            ],
            AppStatus::ACTIVE->value => [
                [
                    'tooltip' => 'Unvalidate',
                    'route' => $modelName,
                    'verb' => 'restore',
                    'icon' => 'pencil-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'modelName' => ['user', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
                [
                    'tooltip' => 'Shop',
                    'route' => 'player-cart',
                    'verb' => 'shop',
                    'icon' => 'plus-square',
                    'admin' => false,
                    'player' => true,
                    'owner' => true,
                    'modelName' => ['player'],
                    'table' => true,
                    'view' => true,
                ],
                [
                    'tooltip' => 'Delete',
                    'route' => $modelName,
                    'verb' => 'delete',
                    'icon' => 'x-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'modelName' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
            ],
        ];
        return $this->render('action-buttons', [
                    'model' => $this->model,
                    'modelName' => $modelName,
                    'actions' => $actions[$this->model->status],
                    'isOwner' => $this->isOwner ?? true,
                    'mode' => $this->mode,
        ]);
    }
}
