<?php

use yii\helpers\Url;
use common\components\ManageAccessRights;

/** @var yii\web\View $this */
/** @var common\model $model */
/** @var string $modelName */
/** @var array $actions */
/** @var bool $isOwner */
/** @var string $mode */

if ($mode === "table") {
    $divClass = 'btn-group';
    $buttonClass = 'btn btn-theme';
} else {
    $divClass = 'actions';
    $buttonClass = 'actions__item';
    $mode = 'view';
}
?>
<div class="<?= $divClass ?>">
    <?php foreach ($actions as $action): ?>

        <?php if (ManageAccessRights::isActionButtonAllowed($action, $modelName, $isOwner, $mode)): ?>

            <a type="button" class="<?= $buttonClass ?>" href="#" data-toggle="tooltip" title="<?= $action['tooltip'] ?>" data-placement="bottom"
               onclick="$('#<?= $action['verb'] ?>-form-<?= $model->id ?>').submit();">
                <i class="bi bi-<?= $action['icon'] ?>"></i>
                <form action="<?= Url::toRoute([$action['route'] . '/' . $action['verb'], 'id' => $model->id]) ?>" id="<?= $action['verb'] ?>-form-<?= $model->id ?>" method="post">
                    <input type="hidden"
                           name="<?= Yii::$app->request->csrfParam ?>" 
                           value="<?= Yii::$app->request->csrfToken ?>">
                </form>
            </a>

        <?php endif; ?>

    <?php endforeach; ?>
</div>
