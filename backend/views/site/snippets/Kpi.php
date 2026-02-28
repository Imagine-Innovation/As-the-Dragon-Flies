<?php

use backend\helpers\KpiHelper;

/** @var yii\web\View $this */
?>
<div class="row row-cols-1 row-cols-md-<?= KpiHelper::mdBreakpoint() ?> row-cols-lg-<?= KpiHelper::lgBreakpoint() ?> row-cols-xxl-<?= KpiHelper::xxlBreakpoint() ?> g-3 mb-4">
    <?php foreach (KpiHelper::KPI as $kpi): ?>
        <div class="col">
            <div class="card card-kpi <?= $kpi['backgroundStyle'] ?> bg-gradient text-white border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 opacity-75"><?= $kpi['title'] ?></h6>
                    <h2 class="card-title mb-0">
                        <span id="<?= $kpi['containerName'] ?>">?</span>
                    </h2>
                    <i class="bi <?= $kpi['icon'] ?> kpi-icon"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
