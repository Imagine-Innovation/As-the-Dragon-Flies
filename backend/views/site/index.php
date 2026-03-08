<?php

use backend\widgets\ActivityGraph;

/** @var yii\web\View $this */
$this->title = Yii::$app->name;
?>
<?= $this->renderFile('@app/views/site/snippets/Kpi.php') ?>

<div class="row g-4">
    <div class="col-12 col-xxl-6" id="activeQuestsTable">
        <?= $this->renderFile('@app/views/site/ajax/active-quests.php') ?>
    </div>

    <div class="col-12 col-xxl-6" id="top10PlayersTable">
        <?= $this->renderFile('@app/views/site/ajax/top10-players.php') ?>
    </div>

    <div class="col-12" id="activityGraph">
        <div class="card">
            <?= ActivityGraph::widget() ?>
        </div>
    </div>
</div>
