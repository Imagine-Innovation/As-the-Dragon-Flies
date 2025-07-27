<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Quest $models */
$playerId = Yii::$app->session->get('playerId');
?>
<div class="row g-4">
    <?php foreach ($models as $model): ?>
        <?php foreach ($model->currentPlayers as $player): ?>
            <div class="col-12 col-sm-6 col-md-12 col-lg-6 col-xl-4 col-xxl-3">
                <div class="image-card">
                    <div class="image-card-body" style="background-image: url('img/characters/<?= $player->image->file_name ?>');">
                        <div class="image-card-label">
                            <h5><?= $player->name ?></h5>
                            <p class="small mb-1"><?= $player->age ?>-year-old <?= $player->gender == 'M' ? 'male' : 'female' ?> <?= $player->race->name ?></p>
                            <p class="small mb-0"><?= $player->level->name ?> <?= $player->alignment->name ?> <?= $player->class->name ?></p>
                            <?php if ($player->id === $playerId && $player->id !== $model->initiator_id): // Initiatoru cannot leave the quest ?>
                                <a href="<?= Url::toRoute(['quest/quit']) ?>" class="btn btn-warning btn-sm mt-2 w-50" type="button">
                                    <i class="bi bi-box-arrow-right"></i> Leave Tavern
                                </a>
                                <button class="btn btn-warning btn-sm mt-2 w-50" id="leaveTavernButton" type="button">
                                    <i class="bi bi-box-arrow-right"></i> Leave Tavern
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
