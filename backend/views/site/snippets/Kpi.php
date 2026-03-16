<?php

use backend\helpers\KpiHelper;
use backend\widgets\Kpi;

/** @var yii\web\View $this */
?>
<div class="row row-cols-1 row-cols-md-<?= KpiHelper::mdBreakpoint() ?> row-cols-lg-<?= KpiHelper::lgBreakpoint() ?> row-cols-xxl-<?= KpiHelper::xxlBreakpoint() ?> g-3 mb-4">
    <?php foreach (KpiHelper::KPI as $kpi): ?>
        <?=
        Kpi::widget([
            'backgroundStyle' => $kpi['backgroundStyle'],
            'title' => $kpi['title'],
            'containerName' => $kpi['containerName'],
            'icon' => $kpi['icon'],
        ])
        ?>
    <?php endforeach; ?>
</div>
