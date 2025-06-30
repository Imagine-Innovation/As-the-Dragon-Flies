<?php
/** @var yii\web\View $this */
/** @var common\models\Quest $models */
$playerId = Yii::$app->session->get('playerId');
?>
<div class="row g-4">
    <?php foreach ($models as $model): ?>
        <?php foreach ($model->currentPlayers as $player): ?>
            <div class="col-12 col-md-6 col-lg-3 col-xl-4">
                <div class="image-card">
                    <div class="image-card-body" style="background-image: url('img/characters/<?= $player->image->file_name ?>');">
                        <div class="image-card-label">
                            <h5><?= $player->name ?></h5>
                            <p class="small mb-1"><?= $player->age ?>-year-old <?= $player->gender == 'M' ? 'male' : 'female' ?> <?= $player->race->name ?></p>
                            <p class="small mb-0"><?= $player->level->name ?> <?= $player->alignment->name ?> <?= $player->class->name ?></p>
                            <?php if ($player->id === $playerId): ?>
                                <a href="" class="btn btn-warning btn-sm mt-2 w-100"><i class="bi bi-box-arrow-right"></i> Leave Tavern</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
