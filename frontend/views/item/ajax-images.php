<?php
/** @var yii\web\View $this */
/** @var common\models\Item $models */
?>
<div class="container-fluid">
    <div class="row g-4">
        <?php foreach ($models as $model): ?>
            <div class="col-12 col-sm-4 col-md-6 col-lg-4 col-xxl-3">
                <div class="card h-100">
                    <?php if ($model->image): ?>
                        <img class="card-img-top" src="img/item/<?= $model->image->file_name ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h6><?= $model->name ?></h6>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
