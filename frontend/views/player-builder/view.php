<?php

use frontend\widgets\PlayerCharacteristics;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container py-3">
    <!-- Character Header -->
    <div class="d-flex align-items-center gap-2 mb-2">
        <div class="avatar-img">
            <img src="img/characters/<?= $model->avatar ?>" class="w-100 h-100 rounded-circle image-thumbnail" style="object-fit: cover;">
        </div>
        <div class="fs-5"><?= $model->name ?>, <?= $model->race->name ?> <?= $model->level->name ?> <?= $model->class->name ?></div>
    </div>

    <?= PlayerCharacteristics::widget(['player' => $model]) ?>

</div>
