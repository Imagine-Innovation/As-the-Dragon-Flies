<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
?>
<!-- Attacks -->
<div class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="fas fa-sword me-2"></i>Attacks & Spells
    </div>
    <div class="card-body">
        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Longsword</h6>
                    <span class="badge btn-fantasy">+8 to hit</span>
                </div>
                <small class="text-muted">1d8+3 slashing damage</small>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Handaxe</h6>
                    <span class="badge btn-fantasy">+6 to hit</span>
                </div>
                <small class="text-muted">1d6+3 slashing damage (thrown 20/60)</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">Crossbow, Light</h6>
                    <span class="badge btn-fantasy">+4 to hit</span>
                </div>
                <small class="text-muted">1d8+1 piercing damage (range 80/320)</small>
            </div>
        </div>
    </div>
</div>
