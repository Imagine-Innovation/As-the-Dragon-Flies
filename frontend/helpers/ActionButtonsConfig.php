<?php

namespace frontend\helpers;

use common\components\AppStatus;

class ActionButtonsConfig
{

    /**
     *
     * @param string $modelName
     * @param int $status
     * @return array<int, array<string, mixed>>
     */
    public static function getActions(string $modelName, int $status): array {
        $actions = [
            AppStatus::DELETED->value => [
                [
                    'tooltip' => 'Restore',
                    'route' => $modelName,
                    'verb' => 'restore',
                    'mode' => 'POST',
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
                    'mode' => 'GET',
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
                    'mode' => 'POST',
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
                    'mode' => 'POST',
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
                    'mode' => 'POST',
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
                    'mode' => 'GET',
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
                    'mode' => 'POST',
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

        return $actions[$status] ?? [];
    }
}
