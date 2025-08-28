<?php

use frontend\widgets\Button;
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
                            <?php if ($player->id !== $model->initiator_id): // Initiator cannot leave the quest  ?>
                                <?php if ($player->id === $playerId): // Only the current player can leave the quest ?>
                                    <?=
                                    Button::widget([
                                        'style' => 'btn-sm mt-2 w-100',
                                        'callToAction' => true,
                                        'id' => 'leaveQuestButton',
                                        'icon' => 'bi-box-arrow-right',
                                        'title' => 'Leave Tavern'
                                    ])
                                    ?>
                                <?php endif; ?>
                            <?php else: // This is the initiator part ?>
                                <?php if ($player->id === $playerId): // only initiator can start the quest ?>
                                    <?=
                                    Button::widget([
                                        'isPost' => true,
                                        'url' => Url::toRoute(['quest/start', 'id' => $model->id]),
                                        'style' => 'btn-sm mt-2 w-100 d-none',
                                        'callToAction' => true,
                                        'id' => 'startQuestButton',
                                        'icon' => 'dnd-action-move',
                                        'title' => 'Start the quest'
                                    ])
                                    ?>

                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
