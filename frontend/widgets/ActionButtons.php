<?php

namespace frontend\widgets;

use common\helpers\Utilities;
use Yii;
use yii\base\Widget;

class ActionButtons extends Widget {

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    public $model;
    public $isOwner;
    public $mode;

    public function run() {
        $modelName = Utilities::modelName($this->model);
        $actions = [
            self::STATUS_DELETED => [
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
            self::STATUS_INACTIVE => [
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
            self::STATUS_ACTIVE => [
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
