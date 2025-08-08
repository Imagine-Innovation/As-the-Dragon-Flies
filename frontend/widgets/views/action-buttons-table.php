<?php

use yii\helpers\Url;
use common\components\ManageAccessRights;

/** @var yii\web\View $this */
/** @var common\model $model */
/** @var string $modelName */
/** @var array $actions */
/** @var bool $isOwner */
?>
<div class="btn-group">
    <?php foreach ($actions as $action): ?>

        <?php if (ManageAccessRights::isActionButtonAllowed($action, $modelName, $isOwner, "table")): ?>

            <?php
            if ($action['mode'] === "POST"):
                $formId = "{$action['verb']}-form-{$model->id}";
                ?>
                <a href="#" role="button" class="btn btn-theme"
                   data-bs-toggle="tooltip" title="<?= $action['tooltip'] ?>" data-placement="bottom"
                   onclick="$('#<?= $formId ?>').submit();">
                    <i class="bi bi-<?= $action['icon'] ?>"></i>
                    <form action="<?= Url::toRoute([$action['route'] . '/' . $action['verb'], 'id' => $model->id]) ?>" id="<?= $formId ?>" method="post">
                        <input type="hidden"
                               name="<?= Yii::$app->request->csrfParam ?>"
                               value="<?= Yii::$app->request->csrfToken ?>">
                    </form>
                </a>

            <?php else: ?>
                <a href="<?= Url::toRoute([$action['route'] . '/' . $action['verb'], 'id' => $model->id]) ?>" role="button" class="btn btn-theme"
                   data-bs-toggle="tooltip" title="<?= $action['tooltip'] ?>" data-placement="bottom">
                    <i class="bi bi-<?= $action['icon'] ?>"></i>
                </a>
            <?php endif; ?>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
