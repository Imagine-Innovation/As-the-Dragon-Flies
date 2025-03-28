<?php
/** @var yii\web\View $this */
/** @var common\models\Quest $models */
?>
<div class="row g-4">
    <?php foreach ($models as $model): ?>
        <?php foreach ($model->players as $player): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <img class="card-img" src="img/characters/<?= $player->image->file_name ?>">
                    <div class="card-img-overlay align-bottom h-100">
                        <div class="card-header">&nbsp;</div>
                        <div class="card-body">&nbsp;</div>
                        <div class="card-header">
                            <h4 class="card-title"><?= $player->name ?></h4>
                            <h4 class="card-subtitle"><?= $player->age ?>-year-old <?= $player->gender == 'M' ? 'male' : 'female' ?> <?= $player->race->name ?></h4>

                            <div>
                                <p>
                                    <span class="badge badge-warning"><?= $player->level->name ?></span>
                                    <span class="badge badge-warning"><?= $player->alignment->name ?></span>
                                    <span class="badge badge-warning"><?= $player->class->name ?></span>
                                </p>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
