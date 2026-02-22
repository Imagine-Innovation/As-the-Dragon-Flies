<?php

use backend\helpers\KpiHelper;

/** @var yii\web\View $this */
$this->title = Yii::$app->name;

$containers = json_encode(KpiHelper::containers());
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
