<?php

use common\components\ManageAccessRights;
use frontend\widgets\IconButton;

/** @var yii\web\View $this */
/** @var common\model $model */
/** @var string $modelName */
/** @var array $actions */
/** @var bool $isOwner */
?>
<div class="actions">
    <?php
    foreach ($actions as $action) {
        if (ManageAccessRights::isActionButtonAllowed($action, $modelName, $isOwner, "view")) {
            if ($action['mode'] === "POST") {
                echo IconButton::widget([
                    'id' => "actionButton-{$action['route']}-{$action['verb']}-{$model->id}",
                    'icon' => "bi bi-{$action['icon']}",
                    'tooltip' => $action['tooltip']
                ]);
            } else {
                echo IconButton::widget([
                    'url' => Url::toRoute([$action['route'] . '/' . $action['verb'], 'id' => $model->id]),
                    'icon' => "bi bi-{$action['icon']}",
                    'tooltip' => $action['tooltip']
                ]);
            }
        }
    }
    ?>
</div>
