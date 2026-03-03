<?php

use backend\widgets\ActivityGraph;

/** @var yii\web\View $this */
$this->title = Yii::$app->name;
?>
<?= $this->renderFile('@app/views/site/snippets/Kpi.php') ?>

<div class="row row-cols-1 row-cols-xxl-2 g-4">
    <div class="col" id="activeQuestsTable">
        <?= $this->renderFile('@app/views/site/ajax/active-quests.php') ?>
    </div>

    <div class="col" id="top10PlayersTable">
        <?= $this->renderFile('@app/views/site/ajax/top10-players.php') ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-activity"></i>
        <span class="fw-semibold">Application Activity</span>
        <span class="text-secondary small ms-1">(last 60 min, 5 min steps)</span>
    </div>
    <div class="card-body p-3">
        <?= ActivityGraph::widget() ?>
    </div>
</div>
