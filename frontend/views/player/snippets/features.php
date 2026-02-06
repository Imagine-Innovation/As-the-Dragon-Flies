<?php

/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
?>
<!-- Features & Traits -->
<div class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi bi-magic me-2"></i>Features & Traits
    </div>
    <div class="card-body">
        <div class="card feature-card mb-3">
            <div class="card-body p-2">
                <h6 class="text-warning mb-2">Second Wind</h6>
                <small class="text-muted">Regain 1d10+5 hit points as a bonus action (1/short rest)</small>
            </div>
        </div>
        <div class="card feature-card mb-3">
            <div class="card-body p-2">
                <h6 class="text-warning mb-2">Action Surge</h6>
                <small class="text-muted">Take an additional action on your turn (1/short rest)</small>
            </div>
        </div>
        <div class="card feature-card mb-3">
            <div class="card-body p-2">
                <h6 class="text-warning mb-2">Darkvision</h6>
                <small class="text-muted">See in dim light within 60 feet as if it were bright light</small>
            </div>
        </div>
        <div class="card feature-card">
            <div class="card-body p-2">
                <h6 class="text-warning mb-2">Dwarven Resilience</h6>
                <small class="text-muted">Advantage against poison saves, resistance to poison damage</small>
            </div>
        </div>
    </div>
</div>
