<?php
/** @var yii\web\View $this */
/** @var common\models\QuestAction $questActions */
?>

<div class="card">
    <div class="card-header">
        What do want to do?
    </div>
    <div class="card-body">
        <?php foreach ($questActions as $questAction): ?>
            <p><?= $questAction->action->name ?></p>
        <?php endforeach; ?>
    </div>
</div>
