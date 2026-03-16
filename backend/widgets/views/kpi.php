<?php
/** @var yii\web\View $this */
/** @var string|null $backgroundStyle */
/** @var string $title */
/** @var string|null $containerName */
/** @var string|null $icon */
/** @var string|null $badge */
/** @var string|null $value */
?>
<div class="col">
    <div class="card card-kpi <?= $backgroundStyle ?? '' ?> bg-gradient text-white border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="card-title text-uppercase small mb-1"><?= $title ?></h6>
                <?php if ($badge): ?>
                    <span class="badge text-bg-secondary badge-metric"><?= $badge ?></span>
                <?php endif; ?>
            </div>
            <h2 class="card-title mb-0">
                <?php if ($containerName): ?>
                    <span id="<?= $containerName ?>"><?= $value ?></span>
                <?php else: ?>
                    <?= $value ?>
                <?php endif; ?>
            </h2>
            <?php if ($icon): ?>
                <i class="bi <?= $icon ?> kpi-icon"></i>
            <?php endif; ?>
        </div>
    </div>
</div>
