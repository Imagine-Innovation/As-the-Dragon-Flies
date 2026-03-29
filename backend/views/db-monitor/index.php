<?php

use backend\helpers\DbMonitorHelper;
use backend\models\DbMonitor;
use backend\widgets\Kpi;
use backend\widgets\DbMonitorTopQueries;
use yii\helpers\Url;

/**
 * @var array{
 *   uptime:int,
 *   threadsConnected:int,
 *   slowQueries:int,
 *   queriesPerSecond:int
 * } $kpis
 * @var array<int, DbMonitor> $topQueries
 */
$explainUrl = Url::to(['db-monitor/ajax-explain']);
$suggestionUrl = Url::to(['db-monitor/ajax-suggestion']);
?>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xxl-4 g-3 mb-4">
    <?=
    Kpi::widget([
        'title' => 'Uptime',
        'value' => DbMonitorHelper::formatUptime($kpis['uptime']),
        'badge' => Yii::$app->db->getDriverName(),
        'icon' => 'bi-clock-history',
    ])
    ?>
    <?=
    Kpi::widget([
        'title' => 'Threads',
        'value' => $kpis['threadsConnected'],
        'icon' => 'bi-cpu',
    ])
    ?>
    <?=
    Kpi::widget([
        'title' => 'Slow Queries',
        'value' => $kpis['slowQueries'],
        'icon' => 'bi-speedometer',
    ])
    ?>
    <?=
    Kpi::widget([
        'title' => 'Queries',
        'value' => $kpis['queriesPerSecond'],
        'icon' => 'bi-database',
    ])
    ?>
</div>

<div class="mt-4">
    <?= DbMonitorTopQueries::widget(['queries' => $topQueries]) ?>
</div>

<!-- Explain Modal -->
<div class="modal fade" id="explainModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Explain Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="explainModalBody"></div>
        </div>
    </div>
</div>

<!-- Suggestion Modal -->
<div class="modal fade" id="suggestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-light">
            <div class="modal-header">
                <h5 class="modal-title">Query Suggestions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="suggestionModalBody"></div>
        </div>
    </div>
</div>

<script>
    function loadExplain(id) {
        fetch('<?= $explainUrl ?>&id=' + id)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('explainModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('explainModal')).show();
                });
    }

    function loadSuggestion(id) {
        fetch('<?= $suggestionUrl ?>&id=' + id)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('suggestionModalBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('suggestionModal')).show();
                });
    }
</script>
