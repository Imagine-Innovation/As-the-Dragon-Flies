<?php

namespace frontend\helpers;

use common\components\AppStatus;

class ActionButtonsConfig
{

    /**
     *
     * @param string $controller
     * @param int $status
     * @return array<int,array{
     *      tooltip: string,
     *      route: string,
     *      verb: string,
     *      mode: string,
     *      icon: string,
     *      admin: bool,
     *      player: bool,
     *      owner: bool,
     *      modelName: array<string>,
     *      table: bool,
     *      view: bool
     * }> $action An associative array containing the action details and requirements.
     *
     */
    public static function getActions(string $controller, int $status): array
    {
        $actions = [
            AppStatus::DELETED->value => [
                [
                    'tooltip' => 'Restore',
                    'controller' => $controller,
                    'action' => 'restore',
                    'mode' => 'POST',
                    'icon' => 'arrow-left-square',
                    'admin' => true,
                    'player' => false,
                    'owner' => false,
                    'controllers' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
            ],
            AppStatus::INACTIVE->value => [
                [
                    'tooltip' => 'View',
                    'controller' => $controller,
                    'action' => 'view',
                    'mode' => 'GET',
                    'icon' => 'info-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'controllers' => ['rule'],
                    'table' => true,
                    'view' => false,
                ],
                [
                    'tooltip' => 'Validate',
                    'controller' => $controller,
                    'action' => 'validate',
                    'mode' => 'POST',
                    'icon' => 'check-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'controllers' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
                [
                    'tooltip' => 'Delete',
                    'controller' => $controller,
                    'action' => 'delete',
                    'mode' => 'POST',
                    'icon' => 'x-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'controllers' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
            ],
            AppStatus::ACTIVE->value => [
                [
                    'tooltip' => 'Unvalidate',
                    'controller' => $controller,
                    'action' => 'restore',
                    'mode' => 'POST',
                    'icon' => 'pencil-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'controllers' => ['user', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
                [
                    'tooltip' => 'Shop',
                    'controller' => 'player-cart',
                    'action' => 'shop',
                    'mode' => 'GET',
                    'icon' => 'plus-square',
                    'admin' => false,
                    'player' => true,
                    'owner' => true,
                    'controllers' => ['player'],
                    'table' => true,
                    'view' => true,
                ],
                [
                    'tooltip' => 'Delete',
                    'controller' => $controller,
                    'action' => 'delete',
                    'mode' => 'POST',
                    'icon' => 'x-square',
                    'admin' => false,
                    'player' => false,
                    'owner' => false,
                    'controllers' => ['user', 'player', 'rule'],
                    'table' => true,
                    'view' => true,
                ],
            ],
        ];

        /** @phpstan-ignore-next-line */
        return $actions[$status] ?? [];
    }
}
