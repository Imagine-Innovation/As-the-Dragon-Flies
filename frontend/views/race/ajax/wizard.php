<?php
/** @var yii\web\View $this */
/** @var common\models\Race $model */
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Congratulation, you are a <?= $model->name ?></h4>
        <?php if ($model->randomImage): ?>
            <div class="clearfix">
                <img class="col-md-6 float-md-end mb-3 ms-md-3" src="img/characters/<?= $model->randomImage ?>" alt="<?= $model->name ?>" style="max-width: 150px;">
                <p class="text-muted"><?= $model->description ?></p>
            </div>
        <?php else: ?>
            <p class="text-muted"><?= $model->description ?></p>
        <?php endif; ?>
    </div>
</div>
