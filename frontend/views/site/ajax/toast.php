<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $UUID */
/** @var string $messageHeader */
/** @var string $message */
/** @var string $severity */
$icons = [
    'error' => '<i class="bi bi-x-circle-fill text-error"></i>',
    'danger' => '<i class="bi bi-stop-circle-fill text-danger"></i>',
    'success' => '<i class="bi bi-check-circle-fill text-sucess"></i>',
    'info' => '<i class="bi bi-info-circle-fill text-info"></i>',
    'warning' => '<i class="bi bi-exclamation-circle-fill text-warning"></i>',
];
?>
<div id="<?= $UUID ?>" class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
    <div class="toast-header">
        <?= $icons[$severity] ?>
        <strong><?= Html::encode($messageHeader) ?></strong>
    </div>
    <div class="toast-body"><?= Html::encode($message) ?></div>
</div>

