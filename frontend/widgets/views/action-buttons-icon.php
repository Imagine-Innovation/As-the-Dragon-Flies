<?php

use common\components\ManageAccessRights;
use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\model $model */
/** @var string $controller */
/** @var array $actions */
/** @var bool $isOwner */
?>
<div class="actions">
    <?php

    foreach ($actions as $action) {
        if (ManageAccessRights::isActionButtonAllowed($action, $controller, $isOwner, 'view')) {
            if ($action['mode'] === 'POST') {
                echo
                    Button::widget([
                        'mode' => 'icon',
                        'id' => "actionButton-{$action['controller']}-{$action['action']}-{$model->id}",
                        'icon' => "bi bi-{$action['icon']}",
                        'tooltip' => $action['tooltip'],
                    ])
                ;
            } else {
                echo
                    Button::widget([
                        'mode' => 'icon',
                        'url' => Url::toRoute([$action['controller'] . '/' . $action['action'], 'id' => $model->id]),
                        'icon' => "bi bi-{$action['icon']}",
                        'tooltip' => $action['tooltip'],
                    ])
                ;
            }
        }
    }
    ?>
</div>
